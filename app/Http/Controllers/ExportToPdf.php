<?php

namespace App\Http\Controllers;

use App\Models\Borrow;
use Illuminate\Http\Request;
use App\Models\StockOut;
use App\Models\StockIn;
use App\Models\SetFrequency;
use App\Models\SetFrequencyDetail;
use App\Models\Unit;
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

    public function printSetFrequencyReport(){
        // $unit = SetFrequency::select('unit')
        //     ->distinct()
        //     ->get()
        //     ->map(function ($item) {
        //     $item->product_count = SetFrequencyDetail::whereHas('setFrequency', function ($query) use ($item) {
        //         $query->where('unit', $item->unit);
        //     })->count();
        //     return $item;
        //     });

        // $details = $unit->map(function ($item) {
        //     $item->products = SetFrequencyDetail::whereHas('setFrequency', function ($query) use ($item) {
        //     $query->where('unit', $item->unit);
        //     })->with('product:id,PID,model_id')->get()->map(function ($detail) {
        //     return [
        //         'PID' => $detail->product->PID,
        //         'model' => $detail->product->model->name,
        //     ];
        //     });
        //     return $item;
        // });

        $unit = SetFrequency::select('unit')
            ->distinct()
            ->get()
            ->map(function ($item) {
            $item->product_count = SetFrequencyDetail::whereHas('setFrequency', function ($query) use ($item) {
                $query->where('unit', $item->unit);
            })->count();
            return $item;
            });

        $details = $unit->map(function ($item) {
            $item->products = SetFrequencyDetail::whereHas('setFrequency', function ($query) use ($item) {
            $query->whereHas('units', function ($unitQuery) use ($item) {
                $unitQuery->where('unit', $item->unit);
            });
            })->with('product:id,PID,model_id', 'product.model:id,name')->get()
            ->groupBy('product.model.name')
            ->map(function ($group, $modelName) {
            return [
                'model' => $modelName,
                'count' => $group->count(),
            ];
            })->values();
            $item->unit_id = SetFrequency::where('unit', $item->unit)->value('unit_id');
            return $item;
        });

        return view('admin.formpdf.set_frequency_detail_report', compact('unit', 'details'));
    }

}
