<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\Product;
use App\Models\Owner;
use App\Models\OwnProducts;

class OwnerController extends Controller
{

    public function index()
    {
        // Logic to retrieve and display the list of owners
        $owners = Owner::with('unit', 'ownProducts')->paginate(5);
        return view('admin.owner.index', compact('owners'));
    }
    public function create()
    {
        // Logic to show the form for creating a new owner
        $units = Unit::orderBy('sort_index')->get();
        $products = Product::orderBy('PID')->get()->map(function ($product) {
            $product->model_name = $product->model->name ?? null;
            return $product;
        });
        return view('admin.owner.create', compact('units', 'products'));
    }

    public function store(Request $request)
    {
        // Logic to store the new owner data
        $request->validate([
            'name' => 'required|string|max:255|unique:owners,name',
            'unit_id' => 'required|exists:units,id',
            'items' => 'required|json',
        ]);

        

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);

        // Check for duplicates in the items
        foreach ($items as $item) {
            $existingProduct = OwnProducts::where('product_id', $item['product_id'])->first();
            if ($existingProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate product found: ' . $item['product_id'],
                ], 400);
            }
        }

        // Create the owner
        $owner = Owner::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'unit_id' => $request->unit_id,
        ]);

        // If no duplicates, proceed to create entries in own_products table
        foreach ($items as $item) {
            // Add product to ownProducts table
            OwnProducts::create([
                'owner_id' => $owner->id,
                'product_id' => $item['product_id'],
            ]);
        }
        

        // Optionally, you can redirect or return a response
        return response()->json([
            'success' => true,
            'message' => 'Owner created successfully.',
            'owner_id' => $owner->id,
        ]);

    }
}
