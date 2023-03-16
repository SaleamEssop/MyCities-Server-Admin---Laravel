<?php

namespace App\Http\Controllers;

use App\Models\Ads;
use App\Models\Site;
use App\Models\User;
use App\Models\Meter;
use App\Models\Account;
use App\Models\Regions;
use App\Models\Settings;
use App\Models\FixedCost;
use App\Models\MeterType;
use App\Models\AccountType;
use App\Models\AdsCategory;
use App\Models\RegionCosts;
use App\Models\RegionAlarms;
use Illuminate\Http\Request;
use App\Models\MeterCategory;
use App\Models\MeterReadings;
use App\Models\AccountFixedCost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\RegionsAccountTypeCost;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class RegionsCostController extends Controller
{
    public function index(Request $request)
    {
        $regionsAccountTypeCost  = [];
        if (Auth::user()->is_super_admin) {
            $regionsAccountTypeCost = RegionsAccountTypeCost::with('region', 'meterType', 'accountType')->get();
            //echo "<pre>";print_r($regionsAccountTypeCost);exit();
        }
        return view('admin.region_cost.index', ['regionsAccountTypeCost' => $regionsAccountTypeCost]);
    }
    public function create(Request $request)
    {
        $regions = Regions::all();
        $meterType = MeterType::all();
        $account_type = AccountType::all();
        return view('admin.region_cost.create', compact('regions', 'meterType', 'account_type'));
    }
    public function store(Request $request)
    {

        $costs = array(
            'region_id' => $request['region_id'],
            'meter_type_id' => $request['meter_type_id'],
            'account_type_id' => $request['account_type_id'],
            'water_in' => json_encode($request->waterin),
            'water_out' => json_encode($request->waterout),
            'garbase_collection_cost' => $request['garbase_collection_cost'],
            'infrastructure_levy_cost' => $request['infrastructure_levy_cost'],
            'vat_percentage' => $request['vat_percentage'],
            'vat_rate' => $request['vat_rate'],
        );

        RegionsAccountTypeCost::create($costs);
        return redirect()->route('region-cost');
    }
    public function edit(Request $request, $id)
    {
        $region_cost = RegionsAccountTypeCost::find($id);

        $regions = Regions::all();
        $meterType = MeterType::all();
        $account_type = AccountType::all();
        return view('admin.region_cost.edit', compact('region_cost', 'regions', 'meterType', 'account_type'));
    }
    public function update(Request $request)
    {
        RegionsAccountTypeCost::where('id', $request->id)->update([
            'region_id' => $request['region_id'],
            'meter_type_id' => $request['meter_type_id'],
            'account_type_id' => $request['account_type_id'],
            'water_in' => json_encode($request->waterin),
            'water_out' => json_encode($request->waterout),
            'garbase_collection_cost' => $request['garbase_collection_cost'],
            'infrastructure_levy_cost' => $request['infrastructure_levy_cost'],
            'vat_percentage' => $request['vat_percentage'],
            'vat_rate' => $request['vat_rate']
        ]);
        return redirect()->route('region-cost');
    }
    public function delete(Request $request)
    {
    }
}
