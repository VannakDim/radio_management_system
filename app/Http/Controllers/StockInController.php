<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductModel;
use App\Models\stock_in_product;
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
            $stock_in->image = 'storage/image/product/stock_in/' . $name_gen;
            // Simulate a long process (e.g., 5 seconds)
            sleep(1);
        }
        $stock_in->save();

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Get the existing details for the stock_in
        $existingDetails = StockInDetail::where('stock_in_id', $stock_in->id)->get();

        // Loop through the items and update or create them in the database
        foreach ($items as $item) {
            $existingDetail = $existingDetails->firstWhere('product_model_id', $item['model_id']);

            if ($existingDetail) {
            // If the quantity is the same, ignore the change
            if ($existingDetail->quantity != $item['quantity']) {
                //Note the change
                $existingDetail->note = 'Updated quantity from ' . $existingDetail->quantity . ' to ' . $item['quantity'] . ' by ' . Auth::user()->name;
                $existingDetail->quantity = $item['quantity'];
                $existingDetail->save();
            }
            } else {
            // Create new detail if it doesn't exist
            StockInDetail::create([
                'stock_in_id' => $stock_in->id,
                'product_model_id' => $item['model_id'],
                'quantity' => $item['quantity'],
            ]);
            }
        }

        // Remove details that are not in the items
        foreach ($existingDetails as $existingDetail) {
            if (!collect($items)->contains('model_id', $existingDetail->product_model_id)) {
                $existingDetail->note = 'Deleted by '. Auth::user()->name;
                //Save the note befor delete
                $existingDetail->save();

                $existingDetail->delete();
            }
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
            $image->storeAs('image/product/stock_in/', $name_gen,'public');
            $stock_in->image = 'storage/image/product/stock_in/' . $name_gen;
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


    public function create_product($id)
    {
        $stock_in = StockIn::findOrFail($id);
        
        $models = ProductModel::select('product_models.*', 'stock_in_details.quantity')
            ->join('stock_in_details', 'product_models.id', '=', 'stock_in_details.product_model_id')
            ->where('stock_in_details.stock_in_id', $id)
            ->whereNull('stock_in_details.deleted_at')
            ->get();
        return view('admin.product.stockin.create_product', compact('stock_in', 'models'));
    }

    public function store_product(Request $request)
    {
        $validated = $request->validate(['stock_in_id' => 'required']);
        $stock_in = StockIn::findOrFail($request->stock_in_id);

        $items = json_decode($request->input('items'), true);

        foreach ($items as $item) {
            $existingProduct = Product::where('PID', $item['serial_number'])->first();
            if ($existingProduct) {
            return response()->json(['message' => 'Duplicate product found: ' . $item['serial_number']], 400);
            }
            Product::create([
            'model_id' => $item['model_id'],
            'PID' => $item['serial_number'],
            ]);
        }
        stock_in_product::create([
            'stock_in_id' => $stock_in->id,
            'product_id' => $request->serial_number,
        ]);

        return response()->json(['message'=> 'Successful!']);
    }

    public function download($id)
    {
        $stock_in = StockIn::findOrFail($id);
        $file_path = public_path($stock_in->image);
        // dd($file_path);

        if (file_exists($file_path)) {
            return response()->download($file_path);
        } else {
            return response()->json(['message' => 'File not found.'], 404);
        }
    }
}
