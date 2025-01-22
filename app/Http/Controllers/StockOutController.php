<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\StockOutProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class StockOutController extends Controller
{

    public function paginateData(Request $request)
    {
        $query = StockOut::with('user');

        // Check if date range is provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }
        
        $stockOut = $query->orderBy('created_at', 'desc')->paginate(5); // 5 items per page
        return response()->json($stockOut);
    }

    // ...existing code...
}
