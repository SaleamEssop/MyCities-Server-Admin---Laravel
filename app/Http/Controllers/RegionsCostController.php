<?php

namespace App\Http\Controllers;

use App\Models\RegionCost;
use App\Models\Regions;
use App\Models\MeterType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RegionsCostController extends Controller
{
    public function index()
    {
        // Get costs with their related Region and Meter Type (e.g. Electricity)
        $costs = RegionCost::with(['region', 'meterType'])
                    ->orderBy('region_id')
                    ->orderBy('meter_type_id')
                    ->orderBy('min')
                    ->get();

        return view('admin.region_cost.index', ['costs' => $costs]);
    }

    public function create()
    {
        return view('admin.region_cost.create', [
            'regions' => Regions::all(),
            'meterTypes' => MeterType::all() // Pass meter types to the view
        ]);
    }

    public function store(Request $request)
    {
        $postData = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'meter_type_id' => 'required|exists:meter_types,id',
            'min' => 'required|numeric|min:0',
            'max' => 'required|numeric|gte:min',
            'amount' => 'required|numeric',
        ]);

        RegionCost::create($postData);

        Session::flash('alert-class', 'alert-success');
        Session::flash('alert-message', 'Tiered Cost added successfully!');
        
        return redirect()->route('region-cost');
    }

    public function delete($id) {
        RegionCost::destroy($id);
        return redirect()->back();
    }
}
