<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use App\Models\StockInDetail;
use App\Models\StockOutDetail;
use App\Models\StockOutProduct;
use App\Models\BorrowDetail;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    /**
     * Get dashboard summary statistics.
     */
    public function getSummary()
    {
        $productModels = ProductModel::with('brand')->get();
        
        $totalStockIn = 0;
        $totalStockOut = 0;
        $totalBorrow = 0;
        $totalAvailable = 0;

        $modelsData = $productModels->map(function ($model) use (&$totalStockIn, &$totalStockOut, &$totalBorrow, &$totalAvailable) {
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

            $available = $stockIn - $stockOut - $borrow;

            // Accumulate totals
            $totalStockIn += $stockIn;
            $totalStockOut += $stockOut;
            $totalBorrow += $borrow;
            $totalAvailable += $available;

            return [
                'id' => $model->id,
                'model_name' => $model->name,
                'frequency' => $model->frequency,
                'type' => $model->type,
                'accessory' => (bool)$model->accessory,
                'capacity' => $model->capacity,
                'power' => $model->power,
                'description' => $model->description,
                'image' => $model->image ? (str_starts_with($model->image, 'storage/') ? $model->image : 'storage/' . $model->image) : null,
                'brand_name' => $model->brand ? $model->brand->brand_name : 'Unknown',
                'stock_in' => (int)$stockIn,
                'stock_out' => (int)$stockOut,
                'borrow' => (int)$borrow,
                'available_stock' => (int)$available,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_models' => $productModels->count(),
                    'total_stock_in' => $totalStockIn,
                    'total_stock_out' => $totalStockOut,
                    'total_borrowed' => $totalBorrow,
                    'total_available' => $totalAvailable,
                ],
                'models' => $modelsData
            ]
        ]);
    }
}
