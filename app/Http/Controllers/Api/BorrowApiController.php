<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\borrow_return;
use App\Models\BorrowDetail;
use App\Models\BorrowAccessory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BorrowApiController extends Controller
{
    /**
     * Get paginated list of borrow records.
     */
    public function getBorrows(Request $request)
    {
        $borrows = Borrow::with(['user', 'details.product.model', 'accessory.model'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $borrows
        ]);
    }

    /**
     * Store a new borrow record.
     */
    public function storeBorrow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver' => 'required|string',
            'purpose' => 'required|string',
            'note' => 'nullable|string',
            'items' => 'required_without:accessories|array', // Devices with serial numbers
            'items.*.model_id' => 'required_with:items|exists:product_models,id',
            'items.*.serial_number' => 'required_with:items|string',
            'accessories' => 'required_without:items|array', // Accessories with quantities
            'accessories.*.model_id' => 'required_with:accessories|exists:product_models,id',
            'accessories.*.quantity' => 'required_with:accessories|integer|min:1',
            'image' => 'nullable|image|max:4096'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $borrow = new Borrow();
            $borrow->user_id = Auth::user()->id;
            $borrow->receiver = $request->receiver;
            $borrow->purpose = $request->purpose;
            $borrow->note = $request->note;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $name_gen = 'br_' . hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('image/product/borrow/', $name_gen, 'public');
                $borrow->image = 'storage/image/product/borrow/' . $name_gen;
            }

            $borrow->save();

            // Process devices (items)
            if ($request->has('items') && is_array($request->input('items'))) {
                foreach ($request->input('items') as $item) {
                    $product = Product::firstOrCreate(
                        ['PID' => $item['serial_number']],
                        ['model_id' => $item['model_id']]
                    );

                    if ($product) {
                        // Check if already borrowed
                        $existingBorrowDetail = BorrowDetail::where('product_id', $product->id)
                            ->where('borrowed', 1)
                            ->first();

                        if ($existingBorrowDetail) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Product with serial number ' . $item['serial_number'] . ' is already borrowed!'
                            ], 400);
                        }

                        BorrowDetail::create([
                            'borrow_id' => $borrow->id,
                            'product_id' => $product->id,
                        ]);
                    }
                }
            }

            // Process accessories
            if ($request->has('accessories')) {
                foreach ($request->input('accessories') as $accessory) {
                    BorrowAccessory::create([
                        'borrow_id' => $borrow->id,
                        'model_id' => $accessory['model_id'],
                        'quantity' => $accessory['quantity'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Borrow record created successfully',
                'data' => $borrow->load('details.product.model', 'accessory.model')
            ], 210);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create borrow record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return borrowed items.
     */
    public function returnBorrow(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'returner_name' => 'required|string',
            'note' => 'nullable|string',
            'items' => 'nullable|array', // Devices being returned
            'items.*.serial_number' => 'required|string',
            'accessories' => 'nullable|array', // Accessories being returned
            'accessories.*.model_id' => 'required|exists:product_models,id',
            'accessories.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $borrow = Borrow::findOrFail($id);

            $return = new borrow_return();
            $return->borrow_id = $borrow->id;
            $return->user_id = Auth::user()->id;
            $return->returner_name = $request->returner_name;
            $return->note = $request->note;
            $return->save();

            $completed_return = true;

            // Handle device returns
            if ($request->has('items')) {
                $existingProductDetails = BorrowDetail::where('borrow_id', $borrow->id)->get()->keyBy('product_id');

                foreach ($request->input('items') as $item) {
                    $product = Product::where('PID', $item['serial_number'])->first();
                    if ($product && isset($existingProductDetails[$product->id])) {
                        $borrowDetail = $existingProductDetails[$product->id];
                        $borrowDetail->log = 'Returned by ' . $request->returner_name . ' | Handler: ' . Auth::user()->name;
                        $borrowDetail->borrowed = 0;
                        $borrowDetail->save();
                    } else {
                        $completed_return = false;
                    }
                }
            }

            // Handle accessories returns
            if ($request->has('accessories')) {
                $existingAccessories = BorrowAccessory::where('borrow_id', $borrow->id)->get()->keyBy('model_id');

                foreach ($request->input('accessories') as $accessory) {
                    $modelId = $accessory['model_id'];
                    if (isset($existingAccessories[$modelId])) {
                        $borrowAccessory = $existingAccessories[$modelId];
                        // If quantity matches or resolves
                        if ($borrowAccessory->quantity == $accessory['quantity']) {
                            $borrowAccessory->log = 'Returned by ' . $request->returner_name . ' | Handler: ' . Auth::user()->name;
                            $borrowAccessory->borrowed = 0;
                            $borrowAccessory->save();
                        } else {
                            $completed_return = false;
                        }
                    } else {
                        $completed_return = false;
                    }
                }
            }

            // Check if all borrowed items are fully returned
            $remainingDevices = BorrowDetail::where('borrow_id', $borrow->id)->where('borrowed', 1)->count();
            $remainingAccessories = BorrowAccessory::where('borrow_id', $borrow->id)->where('borrowed', 1)->count();

            if ($remainingDevices == 0 && $remainingAccessories == 0) {
                $borrow->borrowed = 0;
                $borrow->log = 'Returned 100% back to stock by ' . $request->returner_name . ' | Handler: ' . Auth::user()->name;
                $borrow->save();
            } else {
                $completed_return = false;
                $borrow->log = 'Partially returned by ' . $request->returner_name . ' | Handler: ' . Auth::user()->name;
                $borrow->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $completed_return ? 'All products returned successfully!' : 'Products partially returned.',
                'data' => [
                    'completed' => $completed_return,
                    'remaining_devices' => $remainingDevices,
                    'remaining_accessories' => $remainingAccessories
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process return: ' . $e->getMessage()
            ], 500);
        }
    }
}
