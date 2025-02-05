<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use App\Models\BorrowDetail;
use App\Models\BorrowAccessory;
use App\Models\ProductModel;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
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
            $image->move(public_path('image/product/borrow/'), $name_gen);
            $borrow->image = 'image/product/borrow/' . $name_gen;
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
