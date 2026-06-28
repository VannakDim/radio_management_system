<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\StockOutProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockApiController extends Controller
{
    /**
     * Get paginated list of Stock Ins.
     */
    public function getStockIns(Request $request)
    {
        $stockIns = StockIn::with('user', 'detail.product')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $stockIns
        ]);
    }

    /**
     * Store a new Stock In.
     */
    public function storeStockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_no' => 'nullable|string',
            'supplier' => 'nullable|string',
            'note' => 'nullable|string',
            'items' => 'required|array',
            'items.*.model_id' => 'required|exists:product_models,id',
            'items.*.quantity' => 'required|integer|min:1',
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

            $stock_in = new StockIn();
            $stock_in->user_id = Auth::user()->id;
            $stock_in->invoice_no = $request->invoice_no;
            $stock_in->supplier = $request->supplier;
            $stock_in->note = $request->note;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $name_gen = 'stock_in_' . hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('image/product/stock_in/', $name_gen, 'public');
                $stock_in->image = 'storage/image/product/stock_in/' . $name_gen;
            }

            $stock_in->save();

            foreach ($request->input('items') as $item) {
                StockInDetail::create([
                    'stock_in_id' => $stock_in->id,
                    'product_model_id' => $item['model_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock In created successfully',
                'data' => $stock_in->load('detail.product')
            ], 210);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Stock In record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign specific serial numbers to devices in a Stock In record.
     */
    public function assignStockInProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock_in_id' => 'required|exists:stock_ins,id',
            'items' => 'required|array',
            'items.*.model_id' => 'required|exists:product_models,id',
            'items.*.serial_number' => 'required|string',
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

            $items = $request->input('items');

            // Verify duplicates before inserting
            foreach ($items as $item) {
                $existingProduct = Product::where('PID', $item['serial_number'])->first();
                if ($existingProduct) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Duplicate product found with serial number: ' . $item['serial_number']
                    ], 400);
                }
            }

            // Save products
            foreach ($items as $item) {
                Product::create([
                    'model_id' => $item['model_id'],
                    'PID' => $item['serial_number'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Products registered to stock successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to register products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated list of Stock Outs.
     */
    public function getStockOuts(Request $request)
    {
        $stockOuts = StockOut::with('user', 'products.product.model', 'stockOutDetails.product')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $stockOuts
        ]);
    }

    /**
     * Store a new Stock Out.
     */
    public function storeStockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver' => 'nullable|string',
            'type' => 'nullable|string',
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

            $stock_out = new StockOut();
            $stock_out->user_id = Auth::user()->id;
            $stock_out->receiver = $request->receiver;
            $stock_out->type = $request->type;
            $stock_out->note = $request->note;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $name_gen = 'stock_out_' . hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('image/product/stock_out/', $name_gen, 'public');
                $stock_out->image = 'storage/image/product/stock_out/' . $name_gen;
            }

            $stock_out->save();

            // Process devices (items)
            if ($request->has('items') && is_array($request->input('items'))) {
                foreach ($request->input('items') as $item) {
                    $product = Product::firstOrCreate(
                        ['PID' => $item['serial_number']],
                        ['model_id' => $item['model_id']]
                    );

                    if ($product) {
                        StockOutProduct::create([
                            'stock_out_id' => $stock_out->id,
                            'product_id' => $product->id,
                        ]);
                    }
                }
            }

            // Process accessories
            if ($request->has('accessories')) {
                foreach ($request->input('accessories') as $accessory) {
                    StockOutDetail::create([
                        'stock_out_id' => $stock_out->id,
                        'product_model_id' => $accessory['model_id'],
                        'quantity' => $accessory['quantity'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock Out created successfully',
                'data' => $stock_out->load('products.product.model', 'stockOutDetails.product')
            ], 210);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Stock Out record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a Stock In record (header fields only, owner only).
     */
    public function updateStockIn(Request $request, $id)
    {
        $stockIn = StockIn::find($id);

        if (!$stockIn) {
            return response()->json(['success' => false, 'message' => 'Stock In not found'], 404);
        }

        if ($stockIn->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You can only edit your own records.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'invoice_no' => 'nullable|string',
            'supplier'   => 'nullable|string',
            'note'       => 'nullable|string',
            'image'      => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $stockIn->invoice_no = $request->input('invoice_no', $stockIn->invoice_no);
        $stockIn->supplier   = $request->input('supplier', $stockIn->supplier);
        $stockIn->note       = $request->input('note', $stockIn->note);

        if ($request->hasFile('image')) {
            $image    = $request->file('image');
            $name_gen = 'stock_in_' . hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/stock_in/', $name_gen, 'public');
            $stockIn->image = 'storage/image/product/stock_in/' . $name_gen;
        }

        $stockIn->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock In updated successfully',
            'data'    => $stockIn->load('detail.product'),
        ]);
    }

    /**
     * Update a Stock Out record (header fields only, owner only).
     */
    public function updateStockOut(Request $request, $id)
    {
        $stockOut = StockOut::find($id);

        if (!$stockOut) {
            return response()->json(['success' => false, 'message' => 'Stock Out not found'], 404);
        }

        if ($stockOut->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. You can only edit your own records.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'receiver' => 'nullable|string',
            'type'     => 'nullable|string',
            'note'     => 'nullable|string',
            'image'    => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $stockOut->receiver = $request->input('receiver', $stockOut->receiver);
        $stockOut->type     = $request->input('type', $stockOut->type);
        $stockOut->note     = $request->input('note', $stockOut->note);

        if ($request->hasFile('image')) {
            $image    = $request->file('image');
            $name_gen = 'stock_out_' . hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/stock_out/', $name_gen, 'public');
            $stockOut->image = 'storage/image/product/stock_out/' . $name_gen;
        }

        $stockOut->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock Out updated successfully',
            'data'    => $stockOut->load('products.product.model', 'stockOutDetails.product'),
        ]);
    }
}
