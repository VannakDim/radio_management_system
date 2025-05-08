<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    public function index()
    {
        // Fetch all units from the database
        $units = Unit::orderBy('sort_index')->get();
        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function updateSortIndex(Request $request, $id)
    {
        // Update the sort index of the unit
        $unit = Unit::findOrFail($id);
        $unit->sort_index = $request->input('sort_index');
        $unit->save();

        return redirect()->route('unit.list')->with('success', 'Unit sort index updated successfully.');
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'unit_name' => 'required|string|max:255',
            'sort_index' => 'required|integer',
        ]);

        // Create a new unit
        $unit = new Unit();
        $unit->unit_name = $validatedData['unit_name'];
        $unit->sort_index = $validatedData['sort_index'];
        $unit->save();

        // Redirect to the unit list with a success message
        return redirect()->route('unit.list')->with('success', 'Unit created successfully.');
    }

    public function edit($id)
    {
        // Fetch the unit from the database
        // ...
        $unit = Unit::findOrFail($id);

        return view('product.unit.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        // Update the unit in the database
        // ...
        // return redirect()->route('unit.list')->with('success', 'Unit updated successfully.');
    }

    public function softDelete($id)
    {
        // Soft delete the unit
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('unit.list')->with('success', 'Unit deleted successfully.');
    }
    public function restore($id)
    {
        // Restore the soft-deleted unit
        $unit = Unit::withTrashed()->findOrFail($id);
        $unit->restore();

        return redirect()->route('unit.list')->with('success', 'Unit restored successfully.');
    }
}
