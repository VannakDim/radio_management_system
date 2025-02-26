<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductModel;
use App\Models\SetFrequency;
use App\Models\SetFrequencyDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SetFrequencyController extends Controller
{
    // int function trimester(Date $date){
    //     $setupDate = new \DateTime(now());
    //     $month = (int)$setupDate->format('m');
    //     $trimester = ceil($month / 3);
    //     $year = $setupDate->format('Y');
    //     return $trimester;
    // }
    public function getTrimester($date)
    {
        $setupDate = new \DateTime($date);
        $month = (int)$setupDate->format('m');
        $trimester = ceil($month / 3);
        return $trimester;
    }
    public function create()
    {
        $trimester = $this->getTrimester(now());
        
        $models = ProductModel::all();
        $availableProducts = Product::all();
        return view('admin.product.set_frequency.create', compact('models', 'trimester', 'availableProducts'));
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'unit' => 'required',
            'purpose' => 'required',
            'trimester' => 'required',
            'setup_date' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json(['message' => $validate->errors()], 400);
        };

        $set_frequency = new SetFrequency();
        $set_frequency->user_id = Auth::user()->id;
        $set_frequency->name = $request->name;
        $set_frequency->unit = $request->unit;
        $set_frequency->purpose = $request->purpose;
        $set_frequency->date_of_setup = $request->setup_date;

        $set_frequency->trimester = $request->trimester;


        // Decode the items JSON
        $items = json_decode($request->input('items'), true);
        if (empty($items)) {
            return response()->json(['message' => 'Please add items'], 400);
        }

        $set_frequency->save();

        // return response()->json(['message' => $request->items]);
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
}
