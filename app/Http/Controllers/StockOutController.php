<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\StockOutProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;



class StockOutController extends Controller
{

    public function index()
    {
        $stock_outs = StockOut::with('user')->orderBy('created_at', 'desc')->paginate(5); // 10 items per page
        return view('admin.product.stockout.index', compact('stock_outs'));
    }

    public function paginateData(Request $request)
    {
        $query = StockOut::with('user');

        // Check if date range is provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = date('Y-m-d 00:00:00', strtotime($request->start_date));
            $endDate = date('Y-m-d 23:59:59', strtotime($request->end_date));
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $stockOut = $query->orderBy('created_at', 'desc')->paginate(5); // 5 items per page
        return response()->json($stockOut);
    }

    public function create()
    {
        $models = ProductModel::all();
        $availableProducts = Product::whereNotIn('id', function ($query) {
            $query->select('product_id')->from('borrow_details')->where('borrowed', 1);
        })->whereNotIn('id', function ($query) {
            $query->select('product_id')->from('stock_out_products');
        })->get();
        return view('admin.product.stockout.create', compact('models', 'availableProducts'));
    }

    public function edit($id)
    {
        $stockout = StockOut::with('products', 'stockOutDetails')->findOrFail($id);
        $models = ProductModel::all();
        $availableProducts = Product::whereNotIn('id', function ($query) {
            $query->select('product_id')->from('borrow_details')->where('borrowed', 1);
        })->whereNotIn('id', function ($query) {
            $query->select('product_id')->from('stock_out_products');
        })->get();
        return view('admin.product.stockout.edit', compact('stockout', 'models', 'availableProducts'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate(['supplier' => 'nullable', 'items' => 'required|json']);
        $stock_out = StockOut::findOrFail($id);
        $stock_out->receiver = $request->receiver;
        $stock_out->type = $request->type;
        $stock_out->note = $request->note;
        if ($request->filled('created_at')) {
            $stock_out->created_at = $request->input('created_at');
        }
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_gen = 'stock_out_'. hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/stock_out/', $name_gen, 'public');
            $stock_out->image = 'storage/image/product/stock_out/' . $name_gen;
            // Simulate a long process (e.g., 1 seconds)
            sleep(1);
        }
        $stock_out->save();

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Delete existing stock out products
        $products = StockOutProduct::where('stock_out_id', $stock_out->id)->get();
        foreach ($products as $product) {
            $product->note = 'deleted by user ' . Auth::user()->name;
            $product->save();
            $product->delete();
        }

        if($items) {
            foreach ($items as $item) {
                $product = Product::firstOrCreate(['PID' => $item['serial_number']], ['model_id' => $item['model_id']]);
                if ($product) {
                    StockOutProduct::create([
                        'stock_out_id' => $stock_out->id,
                        'product_id' => $product->id,
                    ]);
                } else {
                    return response()->json(['message' => 'Product not found!']);
                }
            }
        }

        // Decode the accessories JSON
        $accessories = json_decode($request->input('accessories'), true);
            // Delete existing stock out details
            $acc = StockOutDetail::where('stock_out_id', $stock_out->id)->get();
            foreach ($acc as $accessory) {
                $accessory->note = 'deleted by user ' . Auth::user()->name;
                $accessory->save();
                $accessory->delete();
            }

            // Save the new stock out details
            if($accessories) {
                foreach ($accessories as $accessory) {
                    StockOutDetail::create([
                        'stock_out_id' => $stock_out->id,
                        'product_model_id' => $accessory['model_id'],
                        'quantity' => $accessory['quantity'],
                    ]);
                }
            }

        return response()->json(['message' => 'Update Successful!', 'id' => $stock_out->id]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['supplier' => 'nullable', 'items' => 'required|json']);
        $stock_out = new StockOut();
        $stock_out->user_id = Auth::user()->id;
        $stock_out->receiver = $request->receiver;
        $stock_out->type = $request->type;
        $stock_out->note = $request->note;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_gen = 'stock_out_'. hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/stock_out/', $name_gen, 'public');
            $stock_out->image = 'storage/image/product/stock_out/' . $name_gen;
            // Simulate a long process (e.g., 1 seconds)
            sleep(1);
        }
        $stock_out->save();
        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Loop through the items and save them to the database
        foreach ($items as $item) {
            $product = Product::firstOrCreate(['PID' => $item['serial_number']], ['model_id' => $item['model_id']]);
            if ($product) {
                StockOutProduct::create([
                    'stock_out_id' => $stock_out->id,
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
                StockOutDetail::create([
                    'stock_out_id' => $stock_out->id,
                    'product_model_id' => $accessory['model_id'],
                    'quantity' => $accessory['quantity'],
                ]);
            }
        }

        return response()->json(['message' => 'Successful!', 'id' => $stock_out->id]);
    }

    public function uploadImage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $name_gen = 'stock_out_'. hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/stock_out/', $name_gen, 'public');
            $imagePath = 'storage/image/product/stock_out/' . $name_gen;

            $setFrequency = StockOut::findOrFail($id);
            $setFrequency->image = $imagePath;
            $setFrequency->save();

            return back()->with('success', 'Image uploaded and record updated successfully');
        }

        return back()->with('error', 'No image uploaded');
    }

    public function download($id)
    {
        $stockOut = StockOut::findOrFail($id);

        if ($stockOut->image) {
            $filePath = public_path($stockOut->image);
            if (file_exists($filePath)) {
                return response()->download($filePath);
            } else {
                return response()->json(['message' => 'File not found!'], 404);
            }
        } else {
            return response()->json(['message' => 'No image associated with this record!'], 404);
        }
    }

    public function destroy($id)
    {
        $stockOut = StockOut::findOrFail($id);
        $stockOut->delete();

        return response()->json(['message' => 'Deleted successfully!']);
    }
}
