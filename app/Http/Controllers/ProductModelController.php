<?php

namespace App\Http\Controllers;

use App\Models\ProductBrand;
use Illuminate\Http\Request;
use App\Models\ProductModel;
use App\Models\ProductCategory;
use App\Models\StockInDetail;
use App\Models\StockOutProduct;
use App\Models\BorrowDetail;
use App\Models\StockOutDetail;
use Illuminate\Support\Facades\Auth;

class ProductModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = ProductModel::with('brand')->get()->map(function ($model) {
            $stockIn = StockInDetail::where('product_model_id', $model->id)->sum('quantity');
            if ($model->accessory) {
                $stockOut = StockOutDetail::whereHas('product', function ($query) use ($model) {
                    $query->where('product_model_id', $model->id);
                })->sum('quantity');
            } else {
                $stockOut = StockOutProduct::whereHas('product', function ($query) use ($model) {
                    $query->where('model_id', $model->id);
                })->count('id');
            }
            $borrow = BorrowDetail::whereHas('product', function ($query) use ($model) {
                $query->where('model_id', $model->id)
                    ->where('borrowed', 1); // Add condition to filter borrowed products
            })->count('id');
            return [
                'id' => $model->id,
                'model_name' => $model->name,
                'frequency' => $model->frequency,
                'type' => $model->type,
                'image' => $model->image,
                'brand_name' => $model->brand->brand_name,
                'stock_in' => $stockIn,
                'available_stock' => $stockIn - $stockOut - $borrow,
                'stock_out' => $stockOut,
                'borrowed' => $borrow,
            ];
        });
        return view('admin.product.model.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ProductCategory::all();
        $brands = ProductBrand::all();
        return view('admin.product.model.create', compact('brands', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'string|max:50',
            'brands' => 'required|array',
            'name' => 'required',
            'accessory' => 'boolean',
            'frequency' => 'nullable',
            'type' => 'nullable',
            'capacity' => 'nullable',
            'power' => 'nullable',
            'description' => 'nullable',

        ]);

        // Create or fetch categories
        $category = collect($validated['categories'])->map(function ($categoryName) {
            return ProductCategory::firstOrCreate(['name' => $categoryName])->id;
        })->first();
        // Create or fetch brands
        $brand = collect($validated['brands'])->map(function ($brandName) {
            return ProductBrand::firstOrCreate(['brand_name' => $brandName])->id;
        })->first();

        $model = new ProductModel();
        $model->category_id = $category;
        $model->brand_id = $brand;
        $model->name = $request->name;
        $model->accessory = $request->accessory;
        $model->frequency = $request->frequency;
        $model->type = $request->type;
        $model->capacity = $request->capacity;
        $model->power = $request->power;
        $model->description = $request->description;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_gen = 'rd_' . hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/model/', $name_gen , 'public');
            $model->image = 'storage/image/product/model/' . $name_gen;
            // Simulate a long process (e.g., 5 seconds)
            sleep(1);
        }
        $model->save();
        return response()->json(['message' => 'Model created successfully.']);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $categories = ProductCategory::all();
        $brands = ProductBrand::all();
        $model = ProductModel::find($id);
        return view('admin.product.model.edit',compact('model','brands', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'string|max:50',
            'brands' => 'required|array',
            'name' => 'required',
            'accessory' => 'boolean',
            'frequency' => 'nullable',
            'type' => 'nullable',
            'capacity' => 'nullable',
            'power' => 'nullable',
            'description' => 'nullable',
        ]);

        // Create or fetch categories
        $category = collect($validated['categories'])->map(function ($categoryName) {
            return ProductCategory::firstOrCreate(['name' => $categoryName])->id;
        })->first();
        // Create or fetch brands
        $brand = collect($validated['brands'])->map(function ($brandName) {
            return ProductBrand::firstOrCreate(['brand_name' => $brandName])->id;
        })->first();

        $model = ProductModel::find($id);
        $model->category_id = $category;
        $model->brand_id = $brand;
        $model->name = $request->name;
        $model->accessory = $request->accessory;
        $model->frequency = $request->frequency;
        $model->type = $request->type;
        $model->capacity = $request->capacity;
        $model->power = $request->power;
        $model->description = $request->description;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name_gen = 'rd_' . hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('image/product/model/', $name_gen,'public');
            $model->image = 'storage/image/product/model/' . $name_gen;
            // Simulate a long process (e.g., 5 seconds)
            sleep(1);
        }
        $model->save();
        return response()->json(['message' => 'Model updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function softDelete($id)
    {
        $model = ProductModel::find($id);
        $model->description = 'This model has been deleted by user ' . Auth::user()->name;
        $model->save();
        $model->delete();
        return redirect()->back()->with('success', 'Product model deleted successfully.');
    }
}
