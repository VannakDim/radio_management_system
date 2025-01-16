<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOut;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportToPdf extends Controller
{

public function exportStockOutPdf($id)
{
    $stockOut = StockOut::findOrFail($id);

    $pdf = PDF::loadView('admin.formpdf.stockout', compact('stockOut'));

    return view('admin.formpdf.stockout', compact('stockOut'));
    // return $pdf->download('stock_out_' . $stockOut->created_at. $stockOut->id . '.pdf');
}
}
