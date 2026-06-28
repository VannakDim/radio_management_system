<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\ProductCategory;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductApiController extends Controller
{
    /**
     * Get list of all product models.
     */
    public function getModels()
    {
        $models = ProductModel::with('brand', 'category')->get()->map(function ($model) {
            return [
                'id' => $model->id,
                'name' => $model->name,
                'accessory' => (bool)$model->accessory,
                'frequency' => $model->frequency,
                'type' => $model->type,
                'image' => $model->image ? (str_starts_with($model->image, 'storage/') ? $model->image : 'storage/' . $model->image) : null,
                'brand' => $model->brand ? [
                    'id' => $model->brand->id,
                    'name' => $model->brand->brand_name,
                ] : null,
                'category' => $model->category ? [
                    'id' => $model->category->id,
                    'name' => $model->category->category_name,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $models
        ]);
    }

    /**
     * Check if a product with a serial number exists.
     */
    public function checkSerialNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $serialNumber = $request->input('serial_number');
        $product = Product::where('PID', $serialNumber)->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => [
                    'id' => $product->id,
                    'serial_number' => $product->PID,
                    'model_id' => $product->model_id,
                    'model_name' => $product->model ? $product->model->name : 'Unknown',
                    'accessory' => $product->model ? (bool)$product->model->accessory : false,
                ]
            ]);
        } else {
            return response()->json([
                'success' => true,
                'exists' => false,
                'message' => 'Serial number not found'
            ]);
        }
    }

    /**
     * Perform a global search across products, frequency setups, and borrows.
     */
    public function globalSearch(Request $request)
    {
        $query = $request->input('q');
        if (!$query || strlen(trim($query)) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'products' => [],
                    'frequencies' => [],
                    'borrows' => [],
                ]
            ]);
        }

        $searchTerm = '%' . trim($query) . '%';

        // 1. Search Products (by PID, model name, brand name)
        $products = Product::where('PID', 'LIKE', $searchTerm)
            ->orWhereHas('model', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                  ->orWhereHas('brand', function ($qb) use ($searchTerm) {
                      $qb->where('brand_name', 'LIKE', $searchTerm);
                  });
            })
            ->with(['model.brand'])
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'type' => 'Product',
                    'title' => $product->model ? $product->model->name : 'Unknown Product',
                    'subtitle' => 'S/N: ' . $product->PID . ($product->model && $product->model->brand ? ' | ' . $product->model->brand->brand_name : ''),
                    'badge' => $product->model && $product->model->accessory ? 'Accessory' : 'Radio',
                    'data' => [
                        'serial_number' => $product->PID,
                        'model_name' => $product->model ? $product->model->name : 'Unknown',
                        'accessory' => $product->model ? (bool)$product->model->accessory : false,
                    ]
                ];
            });

        // 2. Search Frequencies (by setup name, purpose, unit)
        $frequencies = \App\Models\SetFrequency::where('name', 'LIKE', $searchTerm)
            ->orWhere('purpose', 'LIKE', $searchTerm)
            ->orWhere('unit', 'LIKE', $searchTerm)
            ->limit(10)
            ->get()
            ->map(function ($freq) {
                return [
                    'id' => $freq->id,
                    'type' => 'Frequency',
                    'title' => $freq->name,
                    'subtitle' => 'Unit: ' . $freq->unit . ' | Purpose: ' . $freq->purpose,
                    'badge' => $freq->trimester,
                    'data' => [
                        'id' => $freq->id,
                        'name' => $freq->name,
                        'unit' => $freq->unit,
                        'purpose' => $freq->purpose,
                        'trimester' => $freq->trimester,
                    ]
                ];
            });

        // 3. Search Borrows (by receiver, purpose, note)
        $borrows = \App\Models\Borrow::where('receiver', 'LIKE', $searchTerm)
            ->orWhere('purpose', 'LIKE', $searchTerm)
            ->orWhere('note', 'LIKE', $searchTerm)
            ->limit(10)
            ->get()
            ->map(function ($borrow) {
                return [
                    'id' => $borrow->id,
                    'type' => 'Borrow',
                    'title' => 'Receiver: ' . $borrow->receiver,
                    'subtitle' => 'Purpose: ' . $borrow->purpose . ($borrow->note ? ' | Note: ' . $borrow->note : ''),
                    'badge' => $borrow->returned ? 'Returned' : 'Active',
                    'data' => [
                        'id' => $borrow->id,
                        'receiver' => $borrow->receiver,
                        'purpose' => $borrow->purpose,
                        'note' => $borrow->note,
                        'returned' => (bool)$borrow->returned,
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'frequencies' => $frequencies,
                'borrows' => $borrows,
            ]
        ]);
    }
}
