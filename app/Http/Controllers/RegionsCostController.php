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

        $is_water = 0;
        $water_used = 0;
        $water_in = NULL;
        $water_out = NULL;
        if ($request['is_water'] == 'on') {
            $is_water = 1;
            $water_used = $request['water_used'];
            $water_in = json_encode($request->waterin);
            $water_out = json_encode($request->waterout);
        }
        $is_electricity = 0;
        $electricity = NULL;
        $electricity_used = 0;
        if ($request['is_electricity'] == 'on') {
            $is_electricity = 1;
            $electricity_used = $request['electricity_used'];
            $electricity = json_encode($request->electricity);
        }
        $costs = array(
            'template_name' => $request['template_name'],
            'region_id' => $request['region_id'],
            'account_type_id' => $request['account_type_id'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'is_water' => $is_water,
            'is_electricity' => $is_electricity,
            'water_used' => $water_used,
            'electricity_used' => $electricity_used,
            'water_in' => $water_in,
            'water_out' => $water_out,
            'electricity' => $electricity,
            'additional' => isset($request->additional) ? json_encode($request->additional) : NULL,
            'vat_percentage' => isset($request['vat_percentage']) ? $request['vat_percentage'] : 0,
            'vat_rate' => isset($request['vat_rate']) ? $request['vat_rate'] : 0,
        );

        $save = RegionsAccountTypeCost::create($costs);
        Session::flash('alert-class', 'alert-success');
        Session::flash('alert-message', 'Success! Region Cost Created successfully!');
        return redirect()->route('region-cost-edit', $save->id);
    }
    public function edit(Request $request, $id)
    {
        $region_cost = RegionsAccountTypeCost::find($id);
        // get calculation
        $region_cost =  $this->calculateWaterBilling($region_cost);

        $regions = Regions::all();
        $account_type = AccountType::all();
        return view('admin.region_cost.edit', compact('region_cost', 'regions', 'account_type'));
    }
    public function update(Request $request)
    {

        $is_water = 0;
        $water_used = 0;
        $water_in = NULL;
        $water_out = NULL;
        if ($request['is_water'] == 'on') {
            $is_water = 1;
            $water_used = $request['water_used'];
            $water_in = json_encode($request->waterin);
            $water_out = json_encode($request->waterout);
        }
        $is_electricity = 0;
        $electricity = NULL;
        $electricity_used = 0;
        if ($request['is_electricity'] == 'on') {
            $is_electricity = 1;
            $electricity_used = $request['electricity_used'];
            $electricity = json_encode($request->electricity);
        }
        RegionsAccountTypeCost::where('id', $request->id)->update([
            'template_name' => $request['template_name'],
            'region_id' => $request['region_id'],
            'account_type_id' => $request['account_type_id'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'is_water' => $is_water,
            'is_electricity' => $is_electricity,
            'water_used' => $water_used,
            'electricity_used' => $electricity_used,
            'water_in' => $water_in,
            'water_out' => $water_out,
            'electricity' => $electricity,
            'additional' => isset($request->additional) ? json_encode($request->additional) : NULL,
            'vat_percentage' => isset($request['vat_percentage']) ? $request['vat_percentage'] : 0,
            'vat_rate' => isset($request['vat_rate']) ? $request['vat_rate'] : 0,
        ]);
        Session::flash('alert-class', 'alert-success');
        Session::flash('alert-message', 'Success! Region Cost Update successfully!');
        return redirect()->route('region-cost-edit', $request->id);
    }
    public function delete(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        DB::beginTransaction();
        try {
            $deleted = RegionsAccountTypeCost::where('id', $id)->first()->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Region Cost deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }
    public function calculateWaterBilling($region_cost)
    {
        $water_remaning = $region_cost->water_used;
        $water_in_total  = 0;
        $sub_total  = 0;
        $water_out_total = 0;
        $electricity_total  = 0;
        if ($region_cost->water_used > 0) {
            // water in logic
            if ($region_cost->water_in) {
                $water_in = json_decode($region_cost->water_in);
                if (isset($water_in) && !empty($water_in)) {
                    foreach ($water_in as $key => $value) {
                        if ($water_remaning > 0) {
                            if ($water_remaning > $value->max) {
                                $water_in[$key]->total = number_format($value->max * $value->cost, 2, '.', ',');
                                $water_in[$key]->water_remeaning = $water_remaning - $value->max;
                                $water_remaning = $water_in[$key]->water_remeaning;
                            } else {
                                $water_in[$key]->total = number_format($water_remaning * $value->cost, 2, '.', ',');
                                $water_in[$key]->water_remeaning = $water_remaning - $value->max >= 0 ? $water_remaning - $value->max : 0;
                                $water_remaning = $water_in[$key]->water_remeaning;
                            }
                        } else {
                            // if water_remaning 0 then consider 0 total
                            $water_in[$key]->total = 0;
                        }
                        $water_in_total += $water_in[$key]->total;
                    }
                    $region_cost->water_in_total = number_format($water_in_total, 2, '.', ',');
                    $region_cost->water_in = json_encode($water_in);
                }
            }

            //water out logic
            if (!empty($region_cost->water_out)) {
                $water_out = json_decode($region_cost->water_out);
                $water_out_remaning = $region_cost->water_used;
                if (isset($water_out) && !empty($water_out)) {
                    foreach ($water_out as $key => $value) {
                        if ($water_out_remaning > 0) {
                            if ($water_out_remaning > $value->max) {
                                $t = ($value->max / 100 * $value->percentage) * $value->cost;
                                $water_out[$key]->total = number_format($t, 2, '.', ',');
                                $water_out[$key]->water_remeaning = $water_out_remaning - $value->max;
                                $water_out_remaning = $water_out[$key]->water_remeaning;
                            } else {
                                $ti = ($water_out_remaning / 100 * $value->percentage) * $value->cost;
                                $water_out[$key]->total = number_format($ti, 2, '.', ',');
                                $water_out[$key]->water_remeaning = $water_out_remaning - $value->max >= 0 ? $water_out_remaning - $value->max : 0;
                                $water_out_remaning = $water_out[$key]->water_remeaning;
                            }
                        } else {
                            // if water_out 0 then consider 0 total
                            $water_out[$key]->total = 0;
                        }
                        $water_out_total += $water_out[$key]->total;
                    }
                    $region_cost->water_out_total = number_format($water_out_total, 2, '.', ',');
                    $region_cost->water_out = json_encode($water_out);
                }
            }
        }

        // electricity
        $electricity_remaning = $region_cost->electricity_used;
        if ($region_cost->electricity_used > 0) {
            $electricity = json_decode($region_cost->electricity);

            if (isset($electricity) && !empty($electricity)) {
                foreach ($electricity as $key => $value) {
                    if ($electricity_remaning > 0) {
                        if ($electricity_remaning > $value->max) {
                            $electricity[$key]->total = number_format($value->max * $value->cost, 2, '.', ',');
                            $electricity[$key]->water_remeaning = $electricity_remaning - $value->max;
                            $electricity_remaning = $electricity[$key]->water_remeaning;
                        } else {
                            $electricity[$key]->total = number_format($electricity_remaning * $value->cost, 2, '.', ',');
                            $electricity[$key]->water_remeaning = $electricity_remaning - $value->max >= 0 ? $electricity_remaning - $value->max : 0;
                            $electricity_remaning = $electricity[$key]->water_remeaning;
                        }
                    } else {
                        // if electricity 0 then consider 0 total
                        $electricity[$key]->total = 0;
                    }
                    $electricity_total += $electricity[$key]->total;
                }

                $region_cost->electricity_total = number_format($electricity_total, 2, '.', ',');
                $region_cost->electricity = json_encode($electricity);
            }
        }
        // additional cost
        $additional = json_decode($region_cost->additional);

        $sub_total = $water_in_total + $water_out_total + $electricity_total;
        if (isset($additional) && !empty($additional)) {
            foreach ($additional as $key => $value) {
                $sub_total += $value->cost;
            }
        }

        $region_cost->sub_total = number_format($sub_total, 2, '.', ',');

        $sub_total_vat = $sub_total * $region_cost->vat_percentage / 100;
        $region_cost->sub_total_vat = number_format($sub_total_vat, 2, '.', ',');

        $final_total  = $sub_total + $sub_total_vat + $region_cost->vat_rate;
        $region_cost->final_total = number_format($final_total, 2, '.', ',');
        return $region_cost;
    }
}
