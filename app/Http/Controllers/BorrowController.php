<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\borrow_return;
use App\Models\BorrowDetail;
use App\Models\BorrowAccessory;
use App\Models\ProductModel;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    public function index()
    {
        $borrows = Borrow::with(['user', 'details.product', 'accessory']);
        $borrows = $borrows->where('borrowed', 1)->orderBy('created_at', 'desc')->paginate(5);
        return view('admin.product.borrow.index', compact('borrows'));
        return response()->json(['message' => $completed_return ? 'Return successful!' : 'Return partially successful!', 'id' => $borrow->id]);
    }

    public function paginateData(Request $request)
    {
        $query = Borrow::with('user');

        // Check if date range is provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $query->where(function ($query) {
            $query->whereHas('accessory', function ($accessory) {
                $accessory->where('borrowed', 1); // Only show items that have accessories
            })
                ->orWhereHas('details', function ($product) {
                    $product->where('borrowed', 1); // Only show items that are not returned
                });
        });

        $borrow = $query->orderBy('created_at', 'desc')->paginate(5); // 5 items per page
        return response()->json($borrow);
    }

    public function edit($id)
    {
        $borrow = Borrow::with(['details.product', 'accessory'])->findOrFail($id);
        $models = ProductModel::all();
        $availableProducts = Product::whereNotIn('id', function ($query) {
            $query->select('product_id')->from('borrow_details')->where('borrowed', 1);
        })->whereNotIn('id', function ($query) {
            $query->select('product_id')->from('stock_out_products');
        })->get();
        // dd($borrow);
        return view('admin.product.borrow.edit', compact('borrow', 'models', 'availableProducts'));
    }

    public function create()
    {

        $models = ProductModel::all();
        $availableProducts = Product::whereNotIn('id', function ($query) {
            $query->select('product_id')->from('borrow_details')->where('borrowed', 1);
        })->whereNotIn('id', function ($query) {
            $query->select('product_id')->from('stock_out_products');
        })->get();

        return view('admin.product.borrow.create', compact('models', 'availableProducts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver' => 'required',
            'purpose' => 'required',
            'note' => 'nullable',
            'image' => 'nullable',
        ]);

        $borrow = new Borrow();
        $borrow->user_id = Auth::user()->id;
        $borrow->receiver = $request->receiver;
        $borrow->purpose = $request->purpose;
        $borrow->note = $request->note;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/borrow/', $name_gen);
            $borrow->image = 'storage/image/product/borrow/' . $name_gen;
            // Simulate a long process (e.g., 1 seconds)
            sleep(1);
        }
        $borrow->save();

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Loop through the items and save them to the database
        foreach ($items as $item) {
            $product = Product::firstOrCreate(['PID' => $item['serial_number']], ['model_id' => $item['model_id']]);
            if ($product) {
                BorrowDetail::create([
                    'borrow_id' => $borrow->id,
                    'product_id' => $product->id,
                ]);
            } else {
                return response()->json(['message' => 'Product not found!']);
            }
        }

        // Decode the accessories JSON
        $accessories = json_decode($request->input('accessories'), true);
        if ($accessories) {
            foreach ($accessories as $accessory) {
                BorrowAccessory::create([
                    'borrow_id' => $borrow->id,
                    'model_id' => $accessory['model_id'],
                    'quantity' => $accessory['quantity'],
                ]);
            }
        }

        return response()->json(['message' => 'Successful!', 'id' => $borrow->id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'receiver' => 'required',
            'purpose' => 'required',
            'note' => 'nullable',
            'image' => 'nullable',
        ]);

        $borrow = Borrow::findOrFail($id);
        $borrow->receiver = $request->receiver;
        $borrow->purpose = $request->purpose;
        $borrow->note = $request->note;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_gen = 'br_' . hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/borrow/', $name_gen, 'public');
            $borrow->image = 'storage/image/product/borrow/' . $name_gen;
            // Simulate a long process (e.g., 1 seconds)
            sleep(1);
        }
        $borrow->save();

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Get existing borrow details
        $existingDetails = BorrowDetail::where('borrow_id', $borrow->id)->get()->keyBy('product_id');

        // Loop through the items and update or create them in the database
        foreach ($items as $item) {
            $product = Product::firstOrCreate(['PID' => $item['serial_number']], ['model_id' => $item['model_id']]);
            if ($product) {
                if (isset($existingDetails[$product->id])) {
                    // If the item exists, update it if necessary
                    $borrowDetail = $existingDetails[$product->id];
                    if ($borrowDetail->product_id != $product->id) {
                        $borrowDetail->product_id = $product->id;
                        $borrowDetail->save();
                    }
                    // Remove the item from the existing details list
                    unset($existingDetails[$product->id]);
                } else {
                    // If the item does not exist, create it
                    BorrowDetail::create([
                        'borrow_id' => $borrow->id,
                        'product_id' => $product->id,
                    ]);
                }
            } else {
                return response()->json(['message' => 'Product not found!']);
            }
        }

        // Delete any remaining existing details that were not in the new items list
        foreach ($existingDetails as $detail) {
            // Update the log field in the borrow_details table
            $detail->log = 'Record has been deleted by ' . Auth::user()->name;
            $detail->save();
            $detail->delete();
        }

        // Decode the accessories JSON
        $accessories = json_decode($request->input('accessories'), true);

        // Get existing borrow accessories
        $existingAccessories = BorrowAccessory::where('borrow_id', $borrow->id)->get()->keyBy('model_id');

        if ($accessories) {
            foreach ($accessories as $accessory) {
                if (isset($existingAccessories[$accessory['model_id']])) {
                    // If the accessory exists, update it if necessary
                    $borrowAccessory = $existingAccessories[$accessory['model_id']];
                    if ($borrowAccessory->quantity != $accessory['quantity']) {
                        $borrowAccessory->quantity = $accessory['quantity'];
                        $borrowAccessory->save();
                    }
                    // Remove the accessory from the existing accessories list
                    unset($existingAccessories[$accessory['model_id']]);
                } else {
                    // If the accessory does not exist, create it
                    BorrowAccessory::create([
                        'borrow_id' => $borrow->id,
                        'model_id' => $accessory['model_id'],
                        'quantity' => $accessory['quantity'],
                    ]);
                }
            }
        }

        // Delete any remaining existing accessories that were not in the new accessories list
        foreach ($existingAccessories as $accessory) {
            // Update the log field in the borrow_accessories table
            $accessory->log = 'Record has been deleted by ' . Auth::user()->name;
            $accessory->save();
            $accessory->delete();
        }

        return response()->json(['message' => 'Update successful!', 'id' => $borrow->id]);
    }

    public function return_index($id)
    {
        $borrow = Borrow::with(['details.product', 'accessory'])->findOrFail($id);
        $models = ProductModel::all();
        $availableProducts = Product::whereNotIn('id', function ($query) {
            $query->select('product_id')->from('borrow_details')->where('borrowed', 1);
        })->whereNotIn('id', function ($query) {
            $query->select('product_id')->from('stock_out_products');
        })->get();
        // dd($borrow);
        return view('admin.product.borrow.return', compact('borrow', 'models', 'availableProducts'));
    }

    public function return(Request $request, $id)
    {
        $completed_return = true;
        $request->validate([
            'returner_name' => 'required',
            'note' => 'nullable',
            'image' => 'nullable',
        ]);

        $borrow = Borrow::findOrFail($id);

        $return = new borrow_return();
        $return->borrow_id = $borrow->id;
        $return->user_id = Auth::user()->id;
        $return->returner_name = $request->returner_name;
        $return->note = $request->note;
        $return->save();

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Get existing borrow details
        $existingDetails = BorrowDetail::where('borrow_id', $borrow->id)->get()->keyBy('product_id');

        // Loop through the items and update or delete them in the database
        foreach ($items as $item) {
            $product = Product::where('PID', $item['serial_number'])->first();
            if ($product) {
            // If the item exists, update it if necessary
            $borrowDetail = $existingDetails[$product->id];
            if ($borrowDetail->product_id == $product->id) {
                $borrowDetail->log = 'Record has been return to stock by ' . $request->returner_name . ' and response by ' . Auth::user()->name;
                $borrowDetail->borrowed = 0;
                $borrowDetail->save();
                $borrowDetail->delete();
            } else {
                $completed_return = false;
            }
            } else {
            return response()->json(['message' => 'Product not found!']);
            }
        }

        // Decode the accessories JSON
        $accessories = json_decode($request->input('accessories'), true);

        // Get existing borrow accessories
        $existingAccessories = BorrowAccessory::where('borrow_id', $borrow->id)->get()->keyBy('model_id');

        if ($accessories) {
            foreach ($accessories as $accessory) {
            $borrowAccessory = $existingAccessories[$accessory['model_id']];
            if ($borrowAccessory->quantity == $accessory['quantity']) {
                $borrowAccessory->log = 'Record has been return to stock by ' . $request->returner_name . ' and response by ' . Auth::user()->name;
                $borrowAccessory->borrowed = 0;
                $borrowAccessory->save();
                $borrowAccessory->delete();
            } else {
                $completed_return = false;
            }
            }
        }

        // Check if the count of items and accessories match the existing details and accessories
        if (count($items) != $existingDetails->count() || count($accessories) != $existingAccessories->count()) {
            $completed_return = false;
        }
        if ($completed_return) {
            $borrow->borrowed = 0;
            $borrow->log = 'Product(s) has been return 100% items back to stock by ' . $request->returner_name . ' and response by ' . Auth::user()->name;
            $borrow->save();
            $borrow->delete();
        }else{
            $borrow->log = 'Some item(s) has been return to stock by ' . $request->returner_name . ' and response by ' . Auth::user()->name;
            $borrow->save();
            return response()->json(['message' => 'Some items are not return!']);
        }

        return response()->json(['message' => $completed_return ? 'Return successful!' : 'Return partially successful!', 'id' => $borrow->id]);
    }

    public function download($id)
    {
        $borrow = Borrow::findOrFail($id);

        if ($borrow->image) {
            $filePath = public_path($borrow->image);
            if (file_exists($filePath)) {
                return response()->download($filePath);
            } else {
                return response()->json(['message' => 'File not found!'], 404);
            }
        } else {
            return response()->json(['message' => 'No image associated with this borrow record!'], 404);
        }
    }
}
