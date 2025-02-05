<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductModel;
use App\Models\StockIn;
use App\Models\StockInDetail;

class StockInController extends Controller
{
    public function index()
    {
        $stock_ins = StockIn::with('user')->orderBy('created_at', 'desc')->paginate(5);
        return view('admin.product.stockin.index', compact('stock_ins'));
    }

    public function create()
    {
        $models = ProductModel::all();
        return view('admin.product.stockin.create', compact('models'));
    }

    public function edit($id)
    {
        $stock_in = StockIn::with('detail')->findOrFail($id);
        $models = ProductModel::all();
        return view('admin.product.stockin.edit', compact('stock_in', 'models'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate(['supplier' => 'nullable', 'items' => 'required|json']);
        $stock_in = StockIn::findOrFail($id);
        $stock_in->invoice_no = $request->invoice_no;
        $stock_in->supplier = $request->supplier;
        $stock_in->note = $request->note;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_gen ='stock_in_'. hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/stock_in/', $name_gen, 'public');
            $stock_in->image = 'image/product/stock_in/' . $name_gen;
            // Simulate a long process (e.g., 5 seconds)
            sleep(1);
        }
        $stock_in->save();

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Delete old details
        StockInDetail::where('stock_in_id', $stock_in->id)->delete();

        // Loop through the items and save them to the database
        foreach ($items as $item) {
            StockInDetail::create([
                'stock_in_id' => $stock_in->id,
                'product_model_id' => $item['model_id'],
                'quantity' => $item['quantity'],
            ]);
        }
        return response()->json(['message'=> 'Update Successful!']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['supplier' => 'nullable', 'items' => 'required|json']);
        $stock_in = new StockIn();
        $stock_in->user_id = Auth::user()->id;
        $stock_in->invoice_no = $request->invoice_no;
        $stock_in->supplier = $request->supplier;
        $stock_in->note = $request->note;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_gen ='stock_in_'. hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs(public_path('image/product/stock_in/'), $name_gen,'public');
            $stock_in->image = 'image/product/stock_in/' . $name_gen;
            // Simulate a long process (e.g., 5 seconds)
            sleep(1);
        }
        $stock_in->save();

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Loop through the items and save them to the database
        foreach ($items as $item) {
            StockInDetail::create([
                'stock_in_id' => $stock_in->id,
                'product_model_id' => $item['model_id'],
                'quantity' => $item['quantity'],
            ]);
        }
        return response()->json(['message'=> 'Successful!']);
    }

    public function download($id)
    {
        $stock_in = StockIn::findOrFail($id);
        $file_path = public_path($stock_in->image);

        if (file_exists($file_path)) {
            return response()->download($file_path);
        } else {
            return response()->json(['message' => 'File not found.'], 404);
        }
    }
}
