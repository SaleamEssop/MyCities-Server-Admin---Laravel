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
            $regionsAccountTypeCost = RegionsAccountTypeCost::with('region', 'meterType', 'accountType')->orderBy('id', 'desc')->get();
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
            $water_in = isset($request->waterin) ? json_encode($request->waterin) : NULL;
            $water_out = isset($request->waterout) ? json_encode($request->waterout) : NULL;
        }
        $is_electricity = 0;
        $electricity = NULL;
        $electricity_used = 0;
        if ($request['is_electricity'] == 'on') {
            $is_electricity = 1;
            $electricity_used = $request['electricity_used'];
            $electricity = isset($request->electricity) ? json_encode($request->electricity) : NULL;
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
            'ratable_value' => isset($request['ratable_value']) ? $request['ratable_value'] : 0,
            'billing_date' => $request['billing_date'],
            'reading_date' => $request['reading_date'],
            'waterin_additional' => isset($request->waterin_additional) ? json_encode($request->waterin_additional) : NULL,
            'waterout_additional' => isset($request->waterout_additional) ? json_encode($request->waterout_additional) : NULL,
            'electricity_additional' => isset($request->electricity_additional) ? json_encode($request->electricity_additional) : NULL,
            'water_email' => isset($request['water_email']) ? $request['water_email'] : NULL,
            'electricity_email' => isset($request['electricity_email']) ? $request['electricity_email'] : NULL
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
        //  echo "<pre>";print_r($region_cost);exit();
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
            $water_in = isset($request->waterin) ? json_encode($request->waterin) : NULL;
            $water_out = isset($request->waterout) ? json_encode($request->waterout) : NULL;
        }

        $is_electricity = 0;
        $electricity = NULL;
        $electricity_used = 0;
        if ($request['is_electricity'] == 'on') {
            $is_electricity = 1;
            $electricity_used = $request['electricity_used'];
            $electricity = isset($request->electricity) ? json_encode($request->electricity) : NULL;
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
            'ratable_value' => isset($request['ratable_value']) ? $request['ratable_value'] : 0,
            'billing_date' => $request['billing_date'],
            'reading_date' => $request['reading_date'],
            'waterin_additional' => isset($request->waterin_additional) ? json_encode($request->waterin_additional) : NULL,
            'waterout_additional' => isset($request->waterout_additional) ? json_encode($request->waterout_additional) : NULL,
            'electricity_additional' => isset($request->electricity_additional) ? json_encode($request->electricity_additional) : NULL,
            'water_email' => isset($request['water_email']) ? $request['water_email'] : NULL,
            'electricity_email' => isset($request['electricity_email']) ? $request['electricity_email'] : NULL
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
        $water_in_total  = 0;
        $sub_total  = 0;
        $water_out_total = 0;
        $electricity_total  = 0;
        $sewage_charge = 0;
        $infrastructure_surcharge = 0;
        $water_out_related_total = 0;
        $ratable_value = 0;
        $water_remaning = $region_cost->water_used;
        $rebate = 0;
        $waterin_additional_total = 0;
        $waterout_additional_total = 0;
        $electricity_additional_total = 0;
        if ($region_cost->ratable_value == 0) {
            $ratable_value = 250000;
        } else {
            $ratable_value = $region_cost->ratable_value;
        }


        if ($region_cost->water_used > 0) {
            // water in logic
            // $infrastructure_surcharge = $region_cost->water_used * 1.48;
            //$region_cost->infrastructure_surcharge = number_format($infrastructure_surcharge, 2, '.', '');
            if ($region_cost->water_in) {
                $water_in = json_decode($region_cost->water_in);
                if (isset($water_in) && !empty($water_in)) {
                    foreach ($water_in as $key => $value) {
                        $minmax = $value->max - $value->min;
                        if ($water_remaning > 0) {
                            if (($water_remaning - $minmax) <= 0) {

                                $water_in[$key]->total = number_format($water_remaning * $value->cost, 2, '.', '');
                                $water_in[$key]->water_remeaning = 0;
                                $water_remaning = $water_in[$key]->water_remeaning;
                            } else {

                                $water_in[$key]->total = number_format($minmax * $value->cost, 2, '.', '');
                                $water_in[$key]->water_remeaning = $water_remaning - $minmax;
                                $water_remaning = $water_in[$key]->water_remeaning;
                            }
                        } else {
                            $water_in[$key]->water_remeaning = 0;
                            $water_in[$key]->total = 0;
                        }
                        if ($ratable_value <= 250000 && ($value->max <= 6)) {
                            $water_in[$key]->total = 0;
                        }
                        $water_in_total += $water_in[$key]->total;
                    }
                    $region_cost->water_in_total = number_format($water_in_total, 2, '.', '');
                    $region_cost->water_in = json_encode($water_in);
                }
            }
            // addtional water in logic
            if ($region_cost->waterin_additional) {
                $waterin_additional = json_decode($region_cost->waterin_additional);
                if (isset($waterin_additional) && !empty($waterin_additional)) {
                    foreach ($waterin_additional as $key => $value) {
                        $cal_total = $region_cost->water_used * $value->percentage / 100 * $value->cost;
                        $waterin_additional[$key]->total =  $cal_total;
                        $waterin_additional_total += $cal_total;
                    }
                    $region_cost->water_in_related_total = number_format($waterin_additional_total, 2, '.', '');
                    $region_cost->waterin_additional = json_encode($waterin_additional);
                }
            }
            // echo "<pre>";print_r($waterin_additional_total);exit();

            //water out logic
            if (!empty($region_cost->water_out)) {
                $water_out = json_decode($region_cost->water_out);
                $water_out_remaning = $region_cost->water_used;
                if (isset($water_out) && !empty($water_out)) {
                    foreach ($water_out as $key => $value) {
                        $minmax = $value->max - $value->min;
                        if ($water_out_remaning > 0) {
                            if (($water_out_remaning - $minmax) <= 0) {
                                $t = ($water_out_remaning / 100 * $value->percentage) * $value->cost;
                                $water_out[$key]->total = number_format($t, 2, '.', '');
                                $water_out[$key]->water_remeaning = 0;
                                $water_out_remaning = $water_out[$key]->water_remeaning;
                                // $water_out[$key]->sewage_charge = ($water_out_remaning / 100 * $value->percentage) * 1.48;
                            } else {
                                $t = ($minmax / 100 * $value->percentage) * $value->cost;
                                $water_out[$key]->total = number_format($t, 2, '.', '');
                                $water_out[$key]->water_remeaning = $water_out_remaning - $minmax;
                                $water_out_remaning = $water_out[$key]->water_remeaning;
                                //  $water_out[$key]->sewage_charge = ($minmax / 100 * $value->percentage) * 1.48;
                            }
                        } else {
                            $water_out[$key]->total = 0;
                            // $water_out[$key]->sewage_charge = 0;
                            $water_out[$key]->water_remeaning = 0;
                        }
                        if ($ratable_value <= 250000 && ($value->max <= 6)) {
                            $water_out[$key]->total = 0;
                        }
                        $water_out_total += $water_out[$key]->total;
                        //$sewage_charge += $water_out[$key]->sewage_charge;
                    }
                    $region_cost->water_out_total = number_format($water_out_total, 2, '.', '');
                    // $region_cost->sewage_charge = number_format($sewage_charge, 2, '.', '');
                    $region_cost->water_out = json_encode($water_out);
                }
            }

            // addtional water out logic
            if ($region_cost->waterout_additional) {
                $waterout_additional = json_decode($region_cost->waterout_additional);
                if (isset($waterout_additional) && !empty($waterout_additional)) {
                    foreach ($waterout_additional as $key => $value) {
                        $cal_total = $region_cost->water_used * $value->percentage / 100 * $value->cost;
                        $waterout_additional[$key]->total =  $cal_total;
                        $waterout_additional_total += $cal_total;
                    }
                    $region_cost->water_out_related_total = number_format($waterout_additional_total, 2, '.', '');
                    $region_cost->waterout_additional = json_encode($waterout_additional);
                }
            }
        }

        // electricity
        $electricity_remaning = $region_cost->electricity_used;
        if ($region_cost->electricity_used > 0) {
            $electricity = json_decode($region_cost->electricity);

            if (isset($electricity) && !empty($electricity)) {
                foreach ($electricity as $key => $value) {
                    $minmax = $value->max - $value->min;
                    if ($electricity_remaning > 0) {
                        if (($electricity_remaning - $minmax) <= 0) {
                            //   echo $electricity_remaning;
                            $electricity[$key]->total = number_format($electricity_remaning * $value->cost, 2, '.', '');
                            $electricity[$key]->electricity_remeaning = 0;
                            $electricity_remaning = $electricity[$key]->electricity_remeaning;
                        } else {

                            $electricity[$key]->total = number_format($minmax * $value->cost, 2, '.', '');
                            $electricity[$key]->electricity_remeaning = $electricity_remaning - $minmax;
                            $electricity_remaning = $electricity[$key]->electricity_remeaning;
                        }
                    } else {
                        $electricity[$key]->electricity_remeaning = 0;
                        $electricity[$key]->total = 0;
                    }

                    $electricity_total += $electricity[$key]->total;
                }

                $region_cost->electricity_total = number_format($electricity_total, 2, '.', '');
                $region_cost->electricity = json_encode($electricity);
            }
            // additional cost for electricity
            if ($region_cost->electricity_additional) {
                $electricity_additional = json_decode($region_cost->electricity_additional);
                if (isset($electricity_additional) && !empty($electricity_additional)) {
                    foreach ($electricity_additional as $key => $value) {
                        $cal_total = $region_cost->electricity_used * $value->percentage / 100 * $value->cost;
                        $electricity_additional[$key]->total =  $cal_total;
                        $electricity_additional_total += $cal_total;
                    }
                    $region_cost->electricity_related_total = number_format($electricity_additional_total, 2, '.', '');
                    $region_cost->electricity_additional = json_encode($electricity_additional);
                }
            }
        }
        // additional cost
        $additional = json_decode($region_cost->additional);

        $sub_total = $water_in_total + $water_out_total + $electricity_total + $waterin_additional_total + $waterout_additional_total + $electricity_additional_total;
        if (isset($additional) && !empty($additional)) {
            foreach ($additional as $key => $value) {
                if ($value->cost >= 0) {
                    $sub_total += $value->cost;
                } else {
                    $rebate += $value->cost;
                }
            }
        }


        $subtotal_final = $sub_total - abs($rebate);

        $region_cost->sub_total = number_format($subtotal_final, 2, '.', '');

        $sub_total_vat = $subtotal_final * $region_cost->vat_percentage / 100;
        $region_cost->sub_total_vat = number_format($sub_total_vat, 2, '.', '');

        $final_total  = $subtotal_final + $sub_total_vat + $region_cost->vat_rate;
        $region_cost->final_total = number_format($final_total, 2, '.', '');

        return $region_cost;
    }
    public function copyRecord(Request $request)
    {
        $region_cost = RegionsAccountTypeCost::find($request->id);
        $newPost = $region_cost->replicate();
        $newPost->save();
        Session::flash('alert-class', 'alert-success');
        Session::flash('alert-message', 'Success! Copy Record Created successfully!');
        return redirect()->route('region-cost-edit', $newPost->id);
    }
}
