<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SetFrequency;
use App\Models\StockOut;
use App\Models\Owner;

class SearchApiController extends Controller
{
    /**
     * API endpoint for global search.
     */
    public function apiSearch(Request $request)
    {
        $query = $request->input('q');

        if (is_null($query) || strlen(trim($query)) < 2) {
            return response()->json([
                'success' => true,
                'data' => [
                    'owners' => [],
                    'products' => [],
                    'frequencies' => [],
                    'stock_outs' => [],
                ]
            ]);
        }

        $ownerResults = Owner::with('ownProducts.product')
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhereHas('ownProducts.product', function ($q) use ($query) {
                $q->where('PID', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($owner) {
                $pids = $owner->ownProducts->map(function ($op) {
                    return $op->product ? $op->product->PID : '';
                })->filter()->values()->toArray();

                return [
                    'id' => $owner->id,
                    'type' => 'Owner',
                    'title' => $owner->name,
                    'subtitle' => 'Phone: ' . $owner->phone . (count($pids) > 0 ? ' | Devices: ' . implode(', ', $pids) : ''),
                    'badge' => 'Owner',
                    'data' => [
                        'name' => $owner->name,
                        'phone' => $owner->phone,
                        'pids' => $pids
                    ]
                ];
            });

        $productResults = Product::where('PID', '=', "{$query}")
            ->orWhere('PID', 'LIKE', "%{$query}%")
            ->orWhereHas('model', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->with('model.brand')
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

        $setFrequencyResults = SetFrequency::where('name', 'LIKE', "%{$query}%")
            ->orWhere('purpose', 'LIKE', "%{$query}%")
            ->orWhere('trimester', 'LIKE', "%{$query}%")
            ->orWhere('unit', 'LIKE', "%{$query}%")
            ->orWhereHas('detail.product', function ($queryBuilder) use ($query) {
                $queryBuilder->where('PID', 'LIKE', "%{$query}%");
            })
            ->with('detail.product')
            ->limit(10)
            ->get()
            ->map(function ($freq) {
                $pids = $freq->detail->map(function ($d) {
                    return $d->product ? $d->product->PID : '';
                })->filter()->values()->toArray();

                return [
                    'id' => $freq->id,
                    'type' => 'Frequency',
                    'title' => $freq->name,
                    'subtitle' => 'Unit: ' . $freq->unit . (count($pids) > 0 ? ' | PIDs: ' . implode(', ', $pids) : ' | Purpose: ' . $freq->purpose),
                    'badge' => $freq->trimester,
                    'data' => [
                        'id' => $freq->id,
                        'name' => $freq->name,
                        'unit' => $freq->unit,
                        'purpose' => $freq->purpose,
                        'trimester' => $freq->trimester,
                        'pids' => $pids
                    ]
                ];
            });

        $stockOutResults = StockOut::with('products.product', 'user')
            ->where('receiver', 'LIKE', "%{$query}%")
            ->orWhere('type', 'LIKE', "%{$query}%")
            ->orWhere('note', 'LIKE', "%{$query}%")
            ->orWhereHas('products.product', function ($queryBuilder) use ($query) {
                $queryBuilder->where('PID', 'LIKE', "%{$query}%");
            })
            ->orWhereHas('user', function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($stockOut) {
                $pids = $stockOut->products->map(function ($sp) {
                    return $sp->product ? $sp->product->PID : '';
                })->filter()->values()->toArray();

                return [
                    'id' => $stockOut->id,
                    'type' => 'StockOut',
                    'title' => 'Receiver: ' . $stockOut->receiver,
                    'subtitle' => 'Type: ' . $stockOut->type . (count($pids) > 0 ? ' | PIDs: ' . implode(', ', $pids) : ''),
                    'badge' => 'Stock Out',
                    'data' => [
                        'id' => $stockOut->id,
                        'receiver' => $stockOut->receiver,
                        'type' => $stockOut->type,
                        'note' => $stockOut->note,
                        'pids' => $pids
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'owners' => $ownerResults,
                'products' => $productResults,
                'frequencies' => $setFrequencyResults,
                'stock_outs' => $stockOutResults,
            ]
        ]);
    }
}
