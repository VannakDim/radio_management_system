<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\SetFrequency;

class SearchController extends Controller
{
    public function index()
    {
        return view('admin.search.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        // Perform search logic here (e.g., querying the database)
        // Example: $results = Model::where('name', 'LIKE', "%{$query}%")->orWhere('code', 'LIKE', "%{$query}%")->get();
        // $results = collect();

        $productResults = Product::where('PID', '=', "{$query}")->with('model')->get();
        $productModelResults = ProductModel::where('name', 'LIKE', "%{$query}%")->get();
        $productByModelResults = Product::whereHas('model', function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%");
        })->with('model')->get();
        $brandResults = ProductModel::with('brand')
            ->whereHas('brand', function ($queryBuilder) use ($query) {
                $queryBuilder->where('brand_name', 'LIKE', "%{$query}%");
            })->get();
        $unitResults = SetFrequency::with('units')
        ->whereHas('units', function ($queryBuilder) use ($query) {
            $queryBuilder->where('unit_name', 'LIKE', "%{$query}%");
        })->get();
        
        $setFrequencyResults = SetFrequency::where('name', 'LIKE', "%{$query}%")->with('detail.product')->get();
        $productSetFrequency = SetFrequency::with('detail.product')
            ->whereHas('detail.product', function ($queryBuilder) use ($query) {
                $queryBuilder->where('PID', 'LIKE', "%{$query}%");
            })->first();
        
        return view('admin.search.results', [
            'query' => $query,
            'products' => $productResults,
            'brands' => $brandResults,
            'product_models' => $productModelResults,
            'units' => $unitResults,
            'set_frequencies' => $setFrequencyResults,
            'product_set_frequency' => $productSetFrequency,
            'product_by_model' => $productByModelResults,
        ]);
    }
}