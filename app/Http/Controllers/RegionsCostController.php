<?php

namespace App\Http\Controllers;

use App\Models\RegionCost; // Assuming you have a model for this table
use App\Models\Regions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RegionsCostController extends Controller
{
    public function index()
    {
        // List all costs grouped by region
        return view('admin.region_costs.index', [
            'costs' => RegionCost::with('region')->get()
        ]);
    }

    public function create()
    {
        return view('admin.region_costs.create', [
            'regions' => Regions::all()
        ]);
    }

    public function store(Request $request)
    {
        $postData = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'cost_type' => 'required|string', // e.g., 'electricity', 'water'
            'amount' => 'required|numeric',   // e.g., 2.50
            'valid_from' => 'required|date',
        ]);

        RegionCost::create($postData);

        Session::flash('alert-class', 'alert-success');
        Session::flash('alert-message', 'Region Cost updated successfully!');
        
        return redirect()->route('region-cost');
    }

    // ... (Edit/Update methods would follow similar logic)
}
