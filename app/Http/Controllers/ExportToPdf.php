<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Illuminate\Http\Request;
use App\Models\StockOut;
use App\Models\StockIn;
use App\Models\SetFrequency;
use App\Models\SetFrequencyDetail;
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

    public function printSetFrequency($id)
    {
        $set_frequency = SetFrequency::findOrFail($id);
        $set_frequency_details = SetFrequencyDetail::where('set_frequency_id', $id)->get();

        return view('admin.formpdf.set_frequency', compact('set_frequency', 'set_frequency_details'));
    }

}
