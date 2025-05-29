<?php

namespace App\Http\Controllers;

use App\Models\BorrowAccessory;
use Illuminate\Http\Request;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Models\ProductModel;
use App\Models\StockInDetail;
use App\Models\StockOutProduct;
use App\Models\StockOutDetail;
use App\Models\BorrowDetail;

class DashboardController extends Controller
{
    public function dashboard()
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
                ->where('borrowed', 1);
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
            'borrow' => $borrow,
            ];
        });
        return view('admin.index', compact('data'));
    }
}
