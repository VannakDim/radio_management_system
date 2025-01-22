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
        $query = Borrow::where('borrowed', 1)->with('user');

        // Check if date range is provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $borrows = $query->orderBy('created_at', 'desc')->paginate(5); // 5 items per page
        return response()->json($borrows);
    }
    
    // ...existing code...
}
