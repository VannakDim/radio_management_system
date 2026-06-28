<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitApiController extends Controller
{
    /**
     * Get list of all organization units.
     */
    public function index()
    {
        $units = Unit::orderBy('sort_index')->get()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->unit_name,
                'sort_index' => $unit->sort_index,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }
}
