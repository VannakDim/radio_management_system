<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Illuminate\Http\Request;
use App\Models\StockOut;
use App\Models\StockIn;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportToPdf extends Controller
{

    public function exportStockOutPdf($id)
    {
        $stockOut = StockOut::findOrFail($id);

        $pdf = PDF::loadView('admin.formpdf.stockout', compact('stockOut'));

        return view('admin.formpdf.stockout', compact('stockOut'));
    }

    public function exportBorrow($id)
    {
        $borrow = Borrow::findOrFail($id);

        return view('admin.formpdf.borrow', compact('borrow'));
    }

    public function previewStockIn($id)
    {
        $stockIn = StockIn::findOrFail($id);

        return view('admin.formpdf.preview_stockin', compact('stockIn'));
    }

    public function printReturn($id)
    {
        $borrow = Borrow::findOrFail($id);

        return view('admin.formpdf.return', compact('borrow'));
    }

}
