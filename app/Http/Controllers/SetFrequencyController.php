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

    public function index(Request $request)
    {
        $lastTrimester = SetFrequency::select('trimester')->distinct()->orderBy('trimester', 'desc')->first()->trimester ?? null;
        $set_frequency = SetFrequency::with('user')->orderBy('id', 'desc')->paginate(5);
        $radio = ProductModel::with('brand')->get()->map(function ($model) use ($lastTrimester) {
            $model->product_count = Product::where('model_id', $model->id)
            ->whereHas('setFrequency.setFrequency', function ($query) use ($lastTrimester) {
                $query->where('trimester', $request->trimester ?? $lastTrimester);
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

        $data = SetFrequency::with('units:id,unit_name', 'detail.product.model:id,name')
            ->where('trimester', $lastTrimester)
            ->get()
            ->map(function ($item) {
            $item->detail = $item->detail->map(fn($detail) => [
                'model' => $detail->product->model->name,
                'PID' => $detail->product->PID,
            ]);
            return $item;
            })
            ->sortBy('unit_id')
            ->values();
        

        $details = $unit->map(function ($item) use ($lastTrimester) {
            $item->products = SetFrequencyDetail::whereHas('setFrequency', function ($query) use ($item, $lastTrimester) {
            $query->whereHas('units', function ($unitQuery) use ($item) {
                $unitQuery->where('unit', $item->unit);
            })->where('trimester', $lastTrimester);
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

        $trimesters = SetFrequency::select('trimester')->distinct()->get();

        return view('admin.product.set_frequency.index', compact('set_frequency', 'trimesters', 'radio', 'unit', 'data', 'details'));
    }

    public function changeTrimester(Request $request)
    {
        $lastTrimester = SetFrequency::select('trimester')->distinct()->orderBy('trimester', 'desc')->first()->trimester ?? null;

        $set_frequency = SetFrequency::with('user')->orderBy('id', 'desc')->paginate(5);

        $radio = ProductModel::with('brand')->get()->map(function ($model) use ($request, $lastTrimester) {
            $model->product_count = Product::where('model_id', $model->id)
                ->whereHas('setFrequency.setFrequency', function ($query) use ($request, $lastTrimester) {
                    $query->where('trimester', $request->trimester ?? $lastTrimester);
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

        $data = SetFrequency::with('units:id,unit_name', 'detail.product.model:id,name')
            ->get()
            ->map(function ($item) {
                $item->detail = $item->detail->map(fn($detail) => [
                    'model' => $detail->product->model->name,
                    'PID' => $detail->product->PID,
                ]);
                return $item;
            })
            ->sortBy('unit_id')
            ->values();

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

        $trimesters = SetFrequency::select('trimester')->distinct()->get();

        return response()->json([
            'set_frequency' => $set_frequency,
            'trimesters' => $trimesters,
            'radio' => $radio,
            'unit' => $unit,
            'data' => $data,
            'details' => $details,
        ]);
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
            'unit_id' => 'required|exists:units,id',
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
        $set_frequency->unit_id = $request->unit_id;
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

    public function uploadImage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('uploads/set_frequency_images', $imageName, 'public');

            $setFrequency = SetFrequency::findOrFail($id);
            $setFrequency->image = $imagePath;
            $setFrequency->save();

            return back()->with('success', 'Image uploaded and record updated successfully');
        }

        return back()->with('error', 'No image uploaded');
    }

}
