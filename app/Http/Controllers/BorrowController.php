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
        $borrows = Borrow::with('user')->orderBy('created_at', 'desc')->paginate(5); // 5 items per page
        return response()->json($borrows);
    }
    
    public function create()
    {
        $models = ProductModel::all();
        return view('admin.product.borrow.create', compact('models'));
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
}
