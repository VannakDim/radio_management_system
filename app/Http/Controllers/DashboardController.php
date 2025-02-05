<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Models\ProductModel;
use App\Models\StockInDetail;
use App\Models\StockOutProduct;
use App\Models\BorrowDetail;

class DashboardController extends Controller
{
    public function dashboard(){
        $data = ProductModel::with('brand')->get()->map(function ($model) {
            $stockIn = StockInDetail::where('product_model_id', $model->id)->sum('quantity');
            $stockOut = StockOutProduct::whereHas('product', function ($query) use ($model) {
                $query->where('model_id', $model->id);
            })->count('id');
            $borrow = BorrowDetail::whereHas('product', function ($query) use ($model) {
                $query->where('model_id', $model->id)
                    ->where('borrowed', 1); // Add condition to filter borrowed products
            })->count('id');
            return [
                'id' => $model->id,
                'model_name' => $model->name,
                'image' => $model->image,
                'brand_name' => $model->brand->brand_name,
                'available_stock' => $stockIn - $stockOut - $borrow,
                'borrow' => $borrow,
                'stock_out' => $stockOut,
                'stock_in' => $stockIn,
            ];
        });
        return view('admin.index', compact('data'));
    }
}
