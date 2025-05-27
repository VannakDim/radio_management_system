<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductModel;
use App\Models\Unit;
use App\Models\SetFrequency;
use App\Models\StockOut;

class SearchController extends Controller
{
    public function index()
    {
        return view('admin.search.index');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        if (is_null($query)) {
            return redirect()->back()->with('alert', 'Input your keyword to search');
        }

        // Perform search logic here (e.g., querying the database)
        // Example: $results = Model::where('name', 'LIKE', "%{$query}%")->orWhere('code', 'LIKE', "%{$query}%")->get();
        // $results = collect();


        // All Set Frequency and Product Models
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

        $stockOutResults = StockOut::with('products.product','user')
                    ->where('receiver', 'LIKE', "%{$query}%")
                    ->orWhere('type', 'LIKE', "%{$query}%")
                    ->orWhere('note', 'LIKE', "%{$query}%")
            ->orWhereHas('products.product', function ($queryBuilder) use ($query) {
                $queryBuilder->where('PID', 'LIKE', "%{$query}%");
            })
            ->orWhereHas('user', function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'LIKE', "%{$query}%");
            })
            ->get();
        
        $setFrequencyResults = SetFrequency::where('name', 'LIKE', "%{$query}%")
            ->orWhere('purpose', 'LIKE', "%{$query}%")
            ->orWhere('trimester', 'LIKE', "%{$query}%")
            ->orWhere('unit', 'LIKE', "%{$query}%")
        ->with('detail.product')->get();
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
            'stock_outs' => $stockOutResults,
            'product_set_frequency' => $productSetFrequency,
            'product_by_model' => $productByModelResults,
        ]);
    }
}