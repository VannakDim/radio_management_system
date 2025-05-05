<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\SetFrequency;
use App\Models\SetFrequencyDetail;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Pail\ValueObjects\Origin\Console;

class SetFrequencyController extends Controller
{
   
    public function getTrimester($date)
    {
        $setupDate = new \DateTime($date);
        $month = (int)$setupDate->format('m');
        $trimester = ceil($month / 3);
        return $trimester;
    }

    public function index()
    {
        $set_frequency = SetFrequency::with('user')->orderBy('id', 'desc')->paginate(5);
        $radio = ProductModel::with('brand')->get()->map(function ($model) {
            $model->product_count = Product::where('model_id', $model->id)
            ->whereHas('setFrequency.setFrequency', function ($query) {
                $query->where('trimester', '2025-T2');
            })
            ->count();
            return $model;
        })->sortByDesc('product_count')->values();

        $unit = SetFrequency::select('unit')
            ->distinct()
            ->get()
            ->map(function ($item) {
            $item->product_count = SetFrequencyDetail::whereHas('setFrequency', function ($query) use ($item) {
                $query->where('unit', $item->unit);
            })->count();
            return $item;
            });

        
        $unit = $unit->sortBy('unit_id')->values();

        $data = SetFrequency::with(['units' => function ($query) {
            $query->select('id', 'unit_name');
        }])->get();

        $data = $data->map(function ($item) {
            $item->detail = $item->detail->map(function ($detail) {
                return [
                    'model' => $detail->product->model->name,
                    'PID' => $detail->product->PID,
                ];
            });
            return $item;
        });

        $data = $data->sortBy('unit_id')->values();
        

        $details = $unit->map(function ($item) {
            $item->products = SetFrequencyDetail::whereHas('setFrequency', function ($query) use ($item) {
            $query->where('unit', $item->unit);
            })->with('product:id,PID,model_id')->get()->map(function ($detail) {
            return [
                'PID' => $detail->product->PID,
                'model' => $detail->product->model->name,
            ];
            });
            return $item;
        });

        
        return view('admin.product.set_frequency.index', compact('set_frequency', 'radio', 'unit', 'data', 'details'));
    }

    public function create()
    {
        $trimester = $this->getTrimester(now());
        
        $models = ProductModel::all();
        $units = Unit::all();
        $availableProducts = Product::all();
        return view('admin.product.set_frequency.create', compact('models', 'trimester', 'availableProducts', 'units'));
    }

    public function store(Request $request)
    {
      
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'unit' => 'required',
            'purpose' => 'required',
            'trimester' => 'required',
            'setup_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $set_frequency = new SetFrequency();
        $set_frequency->user_id = Auth::user()->id;
        $set_frequency->name = $request->name;
        $set_frequency->unit = $request->unit;
        $set_frequency->purpose = $request->purpose;
        $set_frequency->date_of_setup = $request->setup_date;
        $set_frequency->trimester = now()->format('Y') . '-T' . $request->trimester;

        // Decode the items JSON
        $items = json_decode($request->input('items'), true);
        if (empty($items)) {
            return response()->json(['message' => 'Please add items'], 400);
        }

        $set_frequency->save();

        foreach ($items as $item) {

            $existingProduct = Product::where('PID', $item['serial_number'])->first();
            if (!$existingProduct) {
                $existingProduct = Product::create([
                    'PID' => $item['serial_number'],
                    'model_id' => $item['model_id'],
                ]);
            }
            SetFrequencyDetail::create([
                'set_frequency_id' => $set_frequency->id,
                'product_id' => $existingProduct->id,
            ]);
        }

        return response()->json(['message' => 'Record add successfully']);
    }

    public function edit($id)
    {
        $set_frequency = SetFrequency::with('user')->find($id);
        $models = ProductModel::all();
        $availableProducts = Product::all();
        return view('admin.product.set_frequency.edit', compact('set_frequency', 'models', 'availableProducts'));
    }

    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required',
    //         'unit' => 'required',
    //         'purpose' => 'required',
    //         'trimester' => 'required',
    //         'setup_date' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 400);
    //     }

    //     $set_frequency = SetFrequency::find($id);
    //     $set_frequency->user_id = Auth::user()->id;
    //     $set_frequency->name = $request->name;
    //     $set_frequency->unit = $request->unit;
    //     $set_frequency->purpose = $request->purpose;
    //     $set_frequency->date_of_setup = $request->setup_date;
    //     $set_frequency->trimester = $request->trimester;

    //     // Decode the items JSON
    //     $items = json_decode($request->input('items'), true);
    //     if (empty($items)) {
    //         return response()->json(['message' => 'Please add items'], 400);
    //     }

    //     // Clear existing details
    //     SetFrequencyDetail::where('set_frequency_id', $id)->delete();

    //     foreach ($items as $item) {
    //         $existingProduct = Product::where('PID', $item['serial_number'])->first();
    //         if (!$existingProduct) {
    //             $existingProduct = Product::create([
    //                 'PID' => $item['serial_number'],
    //                 'model_id' => $item['model_id'],
    //             ]);
    //         }
    //         SetFrequencyDetail::create([
    //             'set_frequency_id' => $set_frequency->id,
    //             'product_id' => $existingProduct->id,
    //         ]);
    //     }

    //     return response()->json(['message' => 'Record updated successfully']);
    // }
    

    
}
