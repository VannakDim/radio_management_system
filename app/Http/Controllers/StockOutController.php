<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\StockOutProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class StockOutController extends Controller
{

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
        return view('admin.product.stockout.create', compact('models'));
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
            $image->move(public_path('image/product/stock_out/'), $name_gen, 'public');
            $stock_out->image = 'image/product/stock_out/' . $name_gen;
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
}
