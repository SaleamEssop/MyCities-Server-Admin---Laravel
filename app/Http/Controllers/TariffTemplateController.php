<?php

namespace App\Http\Controllers;

use App\Models\RegionsAccountTypeCost;
use App\Models\Regions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TariffTemplateController extends Controller
{
    public function index()
    {
        // Get costs with their related Region
        $costs = RegionsAccountTypeCost::with(['region'])
                    ->orderBy('region_id')
                    ->get();

        return view('admin.tariff_template.index', ['costs' => $costs]);
    }

    public function create()
    {
        return view('admin.tariff_template.create', [
            'regions' => Regions::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string|max:255',
            'region_id' => 'required|exists:regions,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Find existing record by id or create new
        if ($request->has('id') && $request->id) {
            $cost = RegionsAccountTypeCost::findOrFail($request->id);
        } else {
            $cost = new RegionsAccountTypeCost();
        }

        // Assign non-array fields
        $cost->fill($request->only([
            'template_name',
            'region_id',
            'start_date',
            'end_date',
            'is_water',
            'is_electricity',
            'water_used',
            'electricity_used',
            'vat_rate',
            'vat_percentage',
            'ratable_value',
            'rates_rebate',
            'is_active',
            'billing_day',
            'read_day',
            'water_email',
            'electricity_email'
        ]));

        // Handle checkbox fields (convert to 1/0)
        $cost->is_water = $request->has('is_water') ? 1 : 0;
        $cost->is_electricity = $request->has('is_electricity') ? 1 : 0;

        // Explicitly assign array fields (Laravel casting handles JSON conversion)
        $cost->water_in = $request->input('waterin', []);
        $cost->water_out = $request->input('waterout', []);
        $cost->electricity = $request->input('electricity', []);
        $cost->additional = $request->input('additional', []);
        $cost->waterin_additional = $request->input('waterin_additional', []);
        $cost->waterout_additional = $request->input('waterout_additional', []);
        $cost->electricity_additional = $request->input('electricity_additional', []);
        
        // Handle new fixed costs and customer costs arrays
        $cost->fixed_costs = $request->input('fixed_costs', []);
        $cost->customer_costs = $request->input('customer_costs', []);

        $cost->save();

        Session::flash('alert-class', 'alert-success');
        Session::flash('alert-message', 'Tariff Template saved successfully!');

        return redirect()->route('tariff-template');
    }

    public function edit($id)
    {
        $tariff_template = RegionsAccountTypeCost::findOrFail($id);

        return view('admin.tariff_template.edit', [
            'tariff_template' => $tariff_template,
            'regions' => Regions::all()
        ]);
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function delete($id)
    {
        RegionsAccountTypeCost::destroy($id);

        Session::flash('alert-class', 'alert-success');
        Session::flash('alert-message', 'Tariff Template deleted successfully!');

        return redirect()->back();
    }

    public function copyRecord(Request $request)
    {
        $original = RegionsAccountTypeCost::findOrFail($request->id);

        $copy = $original->replicate();
        $copy->template_name = $original->template_name . ' (Copy)';
        $copy->save();

        Session::flash('alert-class', 'alert-success');
        Session::flash('alert-message', 'Tariff Template copied successfully!');

        return redirect()->route('tariff-template-edit', ['id' => $copy->id]);
    }
}
