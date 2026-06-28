<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SetFrequency;
use App\Models\SetFrequencyDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FrequencyApiController extends Controller
{
    /**
     * List all frequency setups.
     */
    public function index(Request $request)
    {
        $trimester = $request->input('trimester');

        // 1. Get all distinct trimesters
        $allTrimesters = SetFrequency::select('trimester')
            ->distinct()
            ->orderBy('trimester', 'desc')
            ->get()
            ->pluck('trimester')
            ->toArray();

        // Determine current selected trimester
        $selectedTrimester = $trimester;
        if (!$selectedTrimester) {
            $selectedTrimester = count($allTrimesters) > 0 ? $allTrimesters[0] : null;
        }

        // 2. Query setups for the selected trimester
        $query = SetFrequency::with(['user', 'units', 'detail.product.model']);
        if ($selectedTrimester) {
            $query->where('trimester', $selectedTrimester);
        }
        $frequencies = $query->orderBy('id', 'desc')->paginate($request->input('per_page', 50));

        // 3. Generate unit statistics for the selected trimester ordered by units.sort_index
        $unitStats = [];
        if ($selectedTrimester) {
            $unitStats = SetFrequency::where('trimester', $selectedTrimester)
                ->join('units', 'set_frequencies.unit_id', '=', 'units.id')
                ->select('set_frequencies.unit', 'set_frequencies.unit_id', 'units.sort_index', DB::raw('count(*) as setups_count'))
                ->groupBy('set_frequencies.unit', 'set_frequencies.unit_id', 'units.sort_index')
                ->orderBy('units.sort_index', 'asc')
                ->get()
                ->map(function ($item) use ($selectedTrimester) {
                    $radiosCount = SetFrequencyDetail::whereHas('setFrequency', function ($q) use ($item, $selectedTrimester) {
                        $q->where('unit', $item->unit)->where('trimester', $selectedTrimester);
                    })->count();

                    return [
                        'unit_name' => $item->unit,
                        'setups_count' => $item->setups_count,
                        'radios_count' => $radiosCount,
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'data' => $frequencies,
            'trimesters' => $allTrimesters,
            'selected_trimester' => $selectedTrimester,
            'unit_stats' => $unitStats,
        ]);
    }

    /**
     * Store a new frequency setup.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'unit' => 'required|string',
            'unit_id' => 'required|exists:units,id',
            'purpose' => 'required|string',
            'trimester' => 'required|string', // e.g. "1", "2", "3" or "2026-T1"
            'setup_date' => 'required|date',
            'items' => 'required|array',
            'items.*.serial_number' => 'required|string',
            'items.*.model_id' => 'required|exists:product_models,id',
            'image' => 'nullable|image|max:4096'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $set_frequency = new SetFrequency();
            $set_frequency->user_id = Auth::user()->id;
            $set_frequency->name = $request->name;
            $set_frequency->unit = $request->unit;
            $set_frequency->unit_id = $request->unit_id;
            $set_frequency->purpose = $request->purpose;
            $set_frequency->date_of_setup = $request->setup_date;
            
            // Format trimester standard (matching Laravel backend format now().format('Y') . '-T' . $trimester)
            $trimesterInput = $request->trimester;
            if (!str_contains($trimesterInput, '-T')) {
                $set_frequency->trimester = now()->format('Y') . '-T' . $trimesterInput;
            } else {
                $set_frequency->trimester = $trimesterInput;
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('uploads/set_frequency_images', $imageName, 'public');
                $set_frequency->image = $imagePath;
            }

            $set_frequency->save();

            // Register products and detail links
            foreach ($request->input('items') as $item) {
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

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Frequency configuration set successfully',
                'data' => $set_frequency->load('detail.product.model')
            ], 210);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save frequency record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update/upload image for an existing frequency setup.
     */
    public function updateImage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:4096'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $set_frequency = SetFrequency::find($id);
        if (!$set_frequency) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('uploads/set_frequency_images', $imageName, 'public');
            $set_frequency->image = $imagePath;
            $set_frequency->save();

            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'data' => $set_frequency->load('detail.product.model')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image uploaded'
        ], 400);
    }
}
