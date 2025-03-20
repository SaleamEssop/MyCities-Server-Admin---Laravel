<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ads;
use App\Models\Site;
use App\Models\User;
use App\Models\Meter;
use App\Models\TempId;
use PHPUnit\Exception;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Regions;
use App\Models\Property;
use App\Models\Settings;
use App\Models\FixedCost;
use App\Models\MeterType;
use App\Models\AccountType;
use App\Models\AdsCategory;
use App\Models\RegionCosts;
use App\Models\RegionAlarms;
use Illuminate\Http\Request;
use App\Models\BillingPeriod;
use App\Models\MeterReadings;
use App\Models\AccountFixedCost;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\RegionsAccountTypeCost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ApiController extends Controller
{
    public function addSite(Request $request)
    {


        $postData = $request->post();
        $requiredFields = ['user_id', 'title', 'lat', 'lng', 'address'];
        $validated = validateData($requiredFields, $postData);
        if (!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $siteArr = array(
            'user_id' => $postData['user_id'],
            'title' => $postData['title'],
            'lat' => $postData['lat'],
            'lng' => $postData['lng'],
            'address' => $postData['address']
        );
        if (!empty($postData['email']))
            $siteArr['email'] = $postData['email'];

        $res = Site::create($siteArr);
        if ($res)
            return response()->json(['status' => true, 'code' => 200, 'data' => $res, 'msg' => 'Location added successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function updateSite(Request $request)
    {

        $postData = $request->post();
        $requiredFields = ['site_id', 'user_id', 'title', 'lat', 'lng', 'address'];
        $validated = validateData($requiredFields, $postData);
        if (!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $site = Site::find($postData['site_id']);
        if (empty($site))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, wrong site_id provided!']);

        $site->user_id = $postData['user_id'];
        $site->title = $postData['title'];
        $site->lat = $postData['lat'];
        $site->lng = $postData['lng'];
        $site->address = $postData['address'];

        if (!empty($postData['email']))
            $site->email = $postData['email'];

        if ($site->save())
            return response()->json(['status' => true, 'code' => 200, 'data' => $site, 'msg' => 'Location updated successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getSite(Request $request)
    {


        $postData = $request->post();
        if (empty($postData['user_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, user_id is required!']);

        $sites = Site::where('user_id', $postData['user_id'])->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Location retrieved successfully!', 'data' => $sites]);
    }

    public function deleteSite(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['location_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, location_id is required!']);

        DB::beginTransaction();
        try {
            $result = Site::where('id', $postData['location_id'])->delete();
            $result1 = Account::where('site_id', $postData['location_id'])->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
        }

        if ($result)
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Location removed successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function addAccount(Request $request)
    {
        $postData = $request->post();

        $user_id = $postData['user_id'];

        DB::beginTransaction();
        if (empty($postData['site_id'])) {
            // No site_id has passed, so this must be the new site case
            // Check for required params in case of adding new site
            $requiredFields = ['user_id', 'address'];
            $validated = validateData($requiredFields, $postData);
            if (!$validated['status'])
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Site creation error: ' . $validated['error']]);

            $siteArr = array(
                'user_id' => $postData['user_id'],
                'title' => $postData['title'] ?? "",
                'lat' => $postData['lat'] ?? "",
                'lng' => $postData['lng'] ?? "",
                'address' => $postData['address'],
            );

            $exists = Site::where($siteArr)->first();
            if (!empty($exists)) {
                DB::rollBack();
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, site with same information already exists!']);
            }

            if (!empty($postData['email']))
                $siteArr['email'] = $postData['email'];

            $site = Site::create($siteArr);
            if ($site)
                $postData['site_id'] = $site->id;
            else
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong while creating new site!']);
        }

        $requiredFields = ['user_id', 'site_id', 'account_name', 'account_number', 'optional_information'];
        $validated = validateData($requiredFields, $postData);
        if (!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $accArr = array(
            'site_id' => $postData['site_id'],
            'account_name' => $postData['account_name'],
            'account_number' => $postData['account_number'],
        );

        $exists = Account::where($accArr)->first();
        if (!empty($exists)) {
            DB::rollBack();
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account with same information already exists!']);
        }


        $accArr['user_id'] = $user_id;
        $accArr['region_id'] = $postData['region_id'] ?? null;
        $accArr['account_type_id'] = $postData['account_type_id'] ?? null;
        $accArr['water_email'] = $postData['water_email'] ?? null;
        $accArr['electricity_email'] = $postData['electricity_email'] ?? null;
        $accArr['billing_date'] = $postData['billing_date'] ?? null;
        $accArr['optional_information'] = $postData['optional_information'] ?? null;
        $accArr['bill_day'] = $postData['bill_day'] ?? null;
        $accArr['read_day'] = $postData['read_day'] ?? null;
        $accArr['bill_read_day_active'] = $postData['bill_read_day_active'] ?? null;

        $res = Account::create($accArr);
        if ($res) {
            // Check if user has provided fixed-costs or not
            // if (!empty($postData['fixed_cost'])) {
            //     $fixedCostArr = [];
            //     $n = 0;
            //     foreach ($postData['fixed_cost'] as $fixedCost) {
            //         $fixedCostArr[$n]['account_id'] = $res->id;
            //         $fixedCostArr[$n]['title'] = $fixedCost['name'];
            //         $fixedCostArr[$n]['value'] = $fixedCost['value'];
            //         $fixedCostArr[$n]['added_by'] = $postData['user_id'];
            //         $fixedCostArr[$n]['created_at'] = date("Y-m-d H:i:s");
            //         if (isset($fixedCost['is_active']))
            //             $fixedCostArr[$n]['is_active'] = $postData['is_active'];
            //         $n++;
            //     }
            //     FixedCost::insert($fixedCostArr);
            // }
            $regions_data = RegionsAccountTypeCost::where('region_id', $postData['region_id'])->where('account_type_id', $postData['account_type_id'])->first();
            if (isset($regions_data->additional) && !empty($regions_data->additional)) {
                $addtional = json_decode($regions_data->additional);
                $fixedCostArr = [];
                $n = 0;
                foreach ($addtional as $fixedCost) {
                    $fixedCostArr[$n]['account_id'] = $res->id;
                    $fixedCostArr[$n]['title'] = $fixedCost->name;
                    $fixedCostArr[$n]['value'] = $fixedCost->cost;
                    $fixedCostArr[$n]['added_by'] = $postData['user_id'];
                    $fixedCostArr[$n]['created_at'] = date("Y-m-d H:i:s");
                    $fixedCostArr[$n]['is_active'] = 1;
                    $n++;
                }
                FixedCost::insert($fixedCostArr);
            }

            // Check if there are any default fixed costs
            // if (!empty($postData['default_fixed_cost'])) {
            //     $d = 0;
            //     $defaultCostArr = [];
            //     foreach ($postData['default_fixed_cost'] as $defaultCost) {
            //         $defaultCostArr[$d]['account_id'] = $res->id;
            //         $defaultCostArr[$d]['fixed_cost_id'] = $defaultCost['id'];
            //         $defaultCostArr[$d]['value'] = $defaultCost['value'];
            //         $defaultCostArr[$d]['created_at'] = date("Y-m-d H:i:s");
            //         if (isset($defaultCost['is_active']))
            //             $defaultCostArr[$d]['is_active'] = $defaultCost['is_active'];

            //         $d++;
            //     }
            //     AccountFixedCost::insert($defaultCostArr);
            // }

            // $defaultFixedCosts = Account::join('account_fixed_costs as afc', 'afc.account_id', 'accounts.id')
            //     ->join('fixed_costs as fc', 'fc.id', 'afc.fixed_cost_id')
            //     ->where('accounts.id', $res->id)->get();
            $data = Account::with(['fixedCosts', 'defaultFixedCosts.fixedCost'])->find($res->id);
            // $data->defaultFixedCosts = $defaultFixedCosts;

            DB::commit();
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account added successfully!', 'data' => $data]);
        } else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function updateAccount(Request $request)
    {

        $postData = $request->post();
        DB::beginTransaction();
        if (empty($postData['site_id'])) {
            // No site_id has passed, so this must be the new site case
            // Check for required params in case of adding new site
            $requiredFields = ['user_id', 'title', 'lat', 'lng', 'address'];
            $validated = validateData($requiredFields, $postData);
            if (!$validated['status'])
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Site creation error: ' . $validated['error']]);

            $siteArr = array(
                'user_id' => $postData['user_id'],
                'title' => $postData['title'],
                'lat' => $postData['lat'],
                'lng' => $postData['lng'],
                'address' => $postData['address']
            );
            if (!empty($postData['email']))
                $siteArr['email'] = $postData['email'];

            $exists = Site::where($siteArr)->first();
            if (!empty($exists)) {
                DB::rollBack();
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, site with same information already exists!']);
            }

            $site = Site::create($siteArr);
            if ($site)
                $postData['site_id'] = $site->id;
            else
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong while creating new site!']);
        }

        $requiredFields = ['account_id', 'user_id', 'site_id', 'account_name', 'account_number', 'optional_information'];
        $validated = validateData($requiredFields, $postData);
        if (!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $account = Account::find($postData['account_id']);
        if (empty($account))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, wrong account_id is provided!']);

        $where = [
            'site_id' => $postData['site_id'],
            'account_name' => $postData['account_name'],
            'account_number' => $postData['account_number']
        ];

        $exists = Account::where($where)->where('id', '<>', $postData['account_id'])->first();
        if ($exists) {
            DB::rollBack();
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account with this information already exists!']);
        }

        $account->site_id = $postData['site_id'];
        $account->account_name = $postData['account_name'];
        $account->account_number = $postData['account_number'];
        $account->optional_information = $postData['optional_information'];
        $account->region_id = $postData['region_id'] ?? null;
        $account->account_type_id = $postData['account_type_id'] ?? null;
        $account->water_email = $postData['water_email'] ?? null;
        $account->electricity_email = $postData['electricity_email'] ?? null;
        $account->bill_day = $postData['bill_day'] ?? null;
        $account->read_day = $postData['read_day'] ?? null;
        $account->bill_read_day_active = $postData['bill_read_day_active'] ?? null;
        if (!empty($postData['billing_date']))
            $account->billing_date = $postData['billing_date'];

        if ($account->save()) {
            // Check if user has deleted any fixed costs or not
            if (!empty($postData['removed_fixed_costs_ids'])) {
                $removedCostIDs = $postData['removed_fixed_costs_ids'];
                foreach ($removedCostIDs as $removedCostID)
                    FixedCost::where('id', $removedCostID)->delete();
            }

            // Check if user has provided fixed-costs or not
            if (!empty($postData['fixed_cost'])) {
                FixedCost::where('account_id', $account->id)->delete();
                foreach ($postData['fixed_cost'] as $fixedCost) {
                    $fixedCostArr = array(
                        'account_id' => $account->id,
                        'title' => $fixedCost['name'],
                        'value' => $fixedCost['cost'],
                        // 'added_by' => $postData['user_id'],
                        'created_at' => date("Y-m-d H:i:s")
                    );
                    if (isset($fixedCost['isApplicable']) && $fixedCost['isApplicable'] == true) {
                        $fixedCostArr['is_active'] = 1;
                    } else {
                        $fixedCostArr['is_active'] = 0;
                    }
                    // if (isset($fixedCost['is_active']))
                    //     $fixedCostArr['is_active'] = $fixedCost['is_active'];

                    if (!empty($fixedCost['id']))
                        FixedCost::where('id', $fixedCost['id'])->update($fixedCostArr);
                    else
                        FixedCost::create($fixedCostArr);
                }
                // exit();
            }
            // Check if there are any default fixed costs
            // if (!empty($postData['default_fixed_cost'])) {
            //     foreach ($postData['default_fixed_cost'] as $defaultCost) {
            //         if (empty($defaultCost['id']))
            //             continue;

            //         $upd = array(
            //             'value' => $defaultCost['value']
            //         );

            //         if (isset($defaultCost['is_active']))
            //             $upd['is_active'] = $defaultCost['is_active'];

            //         AccountFixedCost::where('id', $defaultCost['id'])->update($upd);
            //     }
            // }

            $data = Account::with(['fixedCosts', 'defaultFixedCosts.fixedCost'])->find($account->id);

            DB::commit();
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account updated successfully!', 'data' => $data]);
        } else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getAccounts(Request $request)
    {



        $accID = $request->get('account_id');
        if (empty($accID))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);

        // $data = Account::with('site')->where('site_id', $siteID)->get();
        $data = Account::where('id', $accID)->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account retrieved successfully!', 'data' => $data]);
    }






    public function deleteAccount(Request $request)
    {

        $postData = $request->post();


        if (empty($postData['account_id'])) {
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);
        }

        $account = Account::where('id', $postData['account_id'])->first();

        if (!$account) {
            return response()->json(['status' => false, 'code' => 404, 'msg' => 'Account not found!']);
        }
        // Check if the account was created within the last 1 hour
        $oneHourAgo = now()->subHour();

        if ($account->created_at < $oneHourAgo) {
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Account cannot be deleted after one hour of creation.']);
        }

        DB::beginTransaction();
        try {
            // Delete related site if it exists
            if (!empty($account->site_id)) {
                Site::where('id', $account->site_id)->delete();
            }
            // Delete the account
            $account->delete();
            DB::commit();
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account removed successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'code' => 500, 'msg' => 'Oops, something went wrong!']);
        }
    }



    // public function getAllData(Request $request)
    // {

    //     // Get sites, accounts, fixedCosts
    //     if (empty($request->get('user_id')))
    //         return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, user_id is required!']);

    //     $userID = $request->get('user_id');
    //     $defaultCosts = FixedCost::where('is_default', 1)->get();

    //     $data = Site::with(['account' => function ($query) use ($userID) {
    //         $query->where('user_id', $userID);
    //     }, 'account.fixedCosts', 'account.defaultFixedCosts.fixedCost'])
    //         ->where('user_id', $userID)
    //         ->get();
    //     foreach ($data as $site) {
    //         $accounts = $site->account->where('user_id', $userID);
    //         foreach ($accounts as $account) {
    //             $accountDefaultAndOtherCosts = [];
    //             foreach ($account->defaultFixedCosts as $accountCosts) {
    //                 $accountDefaultAndOtherCosts[] = $accountCosts->fixed_cost_id;
    //             }

    //             foreach ($defaultCosts as $defaultCost) {
    //                 // Check if this default cost is assigned to this account or not
    //                 if (in_array($defaultCost->id, $accountDefaultAndOtherCosts)) // Continue as it exists already
    //                     continue;

    //                 // Now add this default cost in this account as well.
    //                 $arr = array(
    //                     'account_id' => $account->id,
    //                     'fixed_cost_id' => $defaultCost->id,
    //                     'is_active' => 1,
    //                 );
    //                 AccountFixedCost::create($arr);
    //                 $account->refresh();
    //             }

    //             /*if (count($account->defaultFixedCosts) == 0 && count($defaultCosts) > 0) {
    //                 // Looks like account default cost relationship has not been set yet
    //                 $arr = [];
    //                 foreach ($defaultCosts as $defaultCost) {
    //                     $arr = array(
    //                         'account_id' => $account->id,
    //                         'fixed_cost_id' => $defaultCost->id,
    //                         'is_active' => 1,

    //                     );
    //                     AccountFixedCost::create($arr);
    //                 }
    //                 $account->refresh();
    //             }*/
    //         }
    //     }

    //     return response()->json(['status' => true, 'code' => 200, 'msg' => 'Data retrieved successfully!', 'data' => $data]);
    // }


    public function getAllData(Request $request)
    {
        // // Get sites, accounts, fixedCosts
        if (empty($request->get('user_id'))) {
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, user_id is required!']);
        }

        $user_id = $request->get('user_id');
        $user = User::where('id', $user_id)->first();

        // Check if user exists
        if (!$user) {
            return response()->json(['status' => false, 'code' => 404, 'msg' => 'User not found!']);
        }
        if ($user->is_property_manager) {
            Log::info('User is property manager');
            $account_id = $user->account_id;
            $userID = $request->get('user_id');
            $defaultCosts = FixedCost::where('is_default', 1)->get();
            $property = Property::where('property_manager_id', $userID)->first();

            if (!$property) {
                return response()->json(['status' => false, 'msg' => 'Property not found'], 404);
            }

            $site_id = $property->site_id;
            $site = Site::with(['account.fixedCosts', 'account.defaultFixedCosts.fixedCost', 'property'])
                ->where('id', $site_id)
                ->first();
            if ($site && $site->account) {
                $account = $site->account->where('id', $account_id)->first();
                if ($account) {
                    $accountDefaultAndOtherCosts = $account->defaultFixedCosts->pluck('fixed_cost_id')->toArray();
                    foreach ($defaultCosts as $defaultCost) {
                        if (in_array($defaultCost->id, $accountDefaultAndOtherCosts)) {
                            continue;
                        }
                        $arr = [
                            'account_id' => $account->id,
                            'fixed_cost_id' => $defaultCost->id,
                            'is_active' => 1,
                        ];
                        AccountFixedCost::create($arr);
                    }
                    $account->refresh();


                    $property = $account->property;
                    $property_details = [
                        'property_name' => $property->name ?? '',
                        'property_manager' => $property->property_manager ? $property->property_manager->name : null,
                        'property_address' => $property->address ?? '',
                        'description' => $property->description ?? '',
                    ];

                    $data = $site->toArray();
                    $data['account'] = [$account->toArray()];
                    $data['property'] = $property_details;

                    return response()->json([
                        'status' => true,
                        'code' => 200,
                        'msg' => 'Data retrieved successfully!',
                        'data' => $data
                    ]);
                    dd($data);
                }
            }

            return response()->json(['status' => false, 'msg' => 'No matching account found'], 404);
        } else {
            Log::info('User is not property manager');

            $userID = $request->get('user_id');

            $defaultCosts = FixedCost::where('is_default', 1)->get();

            $user_accounts = Account::where('user_id', $userID)
                ->select('id', 'site_id')
                ->get();

            $site_ids = array_column($user_accounts->toArray(), 'site_id');

            $data = Site::with(['account' => function ($query) use ($userID) {
                $query->where('user_id', $userID)
                    ->with(['fixedCosts', 'defaultFixedCosts.fixedCost', 'property']);
            }])
                ->whereIn('id', $site_ids)
                ->get();


            foreach ($data as $site) {
                $accounts = $site->account;

                foreach ($accounts as $account) {
                    $accountDefaultAndOtherCosts = [];
                    foreach ($account->defaultFixedCosts as $accountCosts) {
                        $accountDefaultAndOtherCosts[] = $accountCosts->fixed_cost_id;
                    }

                    foreach ($defaultCosts as $defaultCost) {

                        if (in_array($defaultCost->id, $accountDefaultAndOtherCosts))
                            continue;


                        $arr = array(
                            'account_id' => $account->id,
                            'fixed_cost_id' => $defaultCost->id,
                            'is_active' => 1,
                        );
                        AccountFixedCost::create($arr);
                        $account->refresh();
                    }

                    /*if (count($account->defaultFixedCosts) == 0 && count($defaultCosts) > 0) {
                    // Looks like account default cost relationship has not been set yet
                    $arr = [];
                    foreach ($defaultCosts as $defaultCost) {
                        $arr = array(
                            'account_id' => $account->id,
                            'fixed_cost_id' => $defaultCost->id,
                            'is_active' => 1,

                        );
                        AccountFixedCost::create($arr);
                    }
                    $account->refresh();
                }*/
                }
            }
            $data = $data->toArray();

            if (isset($user->account->property)) {
                $property = $user->account->property;
                $property_details = [
                    'property_name' => $property->name ?? '',
                    'property_manager' => $property->property_manager ? $property->property_manager->name : null,
                    'property_address' => $property->address ?? '',
                    'description' => $property->description ?? '',
                ];
            } else {
                $property_details = [
                    'property_name' => '',
                    'property_manager' => null,
                    'property_address' => '',
                    'description' => ''
                ];
            }

            $data = [
                'sites' => $data,
                'property' => $property_details
            ];




            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Data retrieved successfully!', 'data' => $data]);
        }
    }

    public function addMeter(Request $request)
    {


        $postData = $request->post();
        $requiredFields = ['account_id', 'meter_type_id', 'meter_title', 'meter_number', 'meter_reading_date', 'meter_reading'];
        $validated = validateData($requiredFields, $postData);
        if (!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $meterArr = array(
            'account_id' => $postData['account_id'],
            'meter_type_id' => $postData['meter_type_id'],
            'meter_title' => $postData['meter_title'],
            'meter_number' => $postData['meter_number']
        );

        $exists = Meter::where($meterArr)->first();
        if (!empty($exists))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, meter with same information already exists.']);

        $res = Meter::create($meterArr);

        $readingImg = null;

        if ($request->has('meter_reading_image') && !empty($request->input('meter_reading_image'))) {
            $base64Image = $request->input('meter_reading_image');
            if (preg_match('#^data:image/\w+;base64,#i', $base64Image)) {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                $fileName = 'reading_' . time() . '.png';
                $storagePath = 'public/readings/' . $fileName;
                $publicPath = 'storage/readings/' . $fileName;

                Storage::put($storagePath, $imageData);
                $readingImg = $publicPath;
            }
        }

        if ($res) {
            // Add meter reading in system
            $meterReading = array(
                'meter_id' => $res->id,
                //'reading_date' => date('Y-m-d', strtotime($postData['meter_reading_date'])),
                'reading_date' => $postData['meter_reading_date'],
                'reading_value' => $postData['meter_reading'],
                'added_by' => auth()->user()->id,
                'reading_image' => $readingImg
            );

            MeterReadings::create($meterReading);
            $data = Meter::with('readings')->find($res->id);

            return response()->json(['status' => true, 'code' => 200, 'data' => $data, 'msg' => 'Meter added successfully!']);
        } else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function addMeterReadingForm(Request $request)
    {
        $postData = $request->all();
        Log::info('Meter reading form data', ['data' => $postData]);
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Payment added successfully!']);
    }


    //account summary
    public function getAccountSummary(Request $request)
    {
        try {
            $meterId = $request->query('meter_id');

            try {
                $meter = Meter::findOrFail($meterId);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json(['status' => false, 'code' => 404, 'msg' => 'Meter not found!']);
            }

            $account = $meter->account;
            $property = $account->property;
            $billingDay = $property->billing_day;
            $currentDate = now();

            if ($currentDate->day >= $billingDay) {
                $currentCycleEnd = $currentDate->copy()->addMonth()->day($billingDay)->subDay();
            } else {
                $currentCycleEnd = $currentDate->copy()->day($billingDay)->subDay();
            }

            $billingPeriods = [];
            for ($i = 0; $i < 4; $i++) {
                $endDate = $currentCycleEnd->copy()->subMonths($i);
                $startDate = $endDate->copy()->subMonth()->addDay();
                $billingPeriods[] = [
                    'start_date' => $startDate->copy(),
                    'end_date' => $endDate->copy(),
                    'label' => $startDate->format('d M') . ' to ' . $endDate->format('d M'),
                    'value' => $startDate->toDateString() . ' to ' . $endDate->toDateString(),
                ];
            }
            $billingPeriods = array_reverse($billingPeriods);

            $billings = BillingPeriod::where('meter_id', $meterId)->orderBy('start_date')->get();
            $payments = Payment::where('meter_id', $meterId)->orderBy('payment_date')->get();

            $accountSummary = [];
            foreach ($billingPeriods as $index => $period) {
                $startDate = $period['start_date'];
                $endDate = $period['end_date'];

                $periodBillings = $billings->filter(function ($billing) use ($startDate, $endDate) {
                    return $billing->start_date <= $endDate && $billing->end_date >= $startDate;
                });

                $periodPayments = $payments->filter(function ($payment) use ($periodBillings) {
                    return $periodBillings->pluck('id')->contains($payment->billing_period_id);
                });

                $totalPaymentAmount = 0;
                $paidAmount = 0;
                $latestPaymentDate = null;
                $paymentDetails = [];
                foreach ($periodPayments as $payment) {

                    $totalPaymentAmount += (float) $payment->amount;

                    if ($payment->status === 'paid') {
                        $paidAmount += (float) $payment->amount;
                        $latestPaymentDate = $payment->payment_date > $latestPaymentDate ? $payment->payment_date : $latestPaymentDate;
                    } elseif ($payment->status === 'partially_paid') {
                        $paidAmount += (float) ($payment->total_paid_amount ?? 0);
                        $latestPaymentDate = $payment->payment_date > $latestPaymentDate ? $payment->payment_date : $latestPaymentDate;
                    }

                    $paymentDetails[] = [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'paid_amount' => $payment->total_paid_amount ?? 0,
                        'status' => $payment->status,
                        'payment_date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('d M Y') : null,
                        'billing_period_id' => $payment->billing_period_id,
                    ];
                }

                $balanceBf = $index === 0 ? 0 : $accountSummary[$index - 1]['balance_carried'];
                $totalAmountDue = $totalPaymentAmount + $balanceBf;
                $balanceCarried = $totalAmountDue - $paidAmount;

                if ($balanceCarried < 0) {
                    $balanceCarried = 0;
                }

                $formattedPaymentDate = $latestPaymentDate ? Carbon::parse($latestPaymentDate)->format('d M Y') : null;
                $isOverdue = $balanceCarried > 0 && $endDate < $currentDate;

                $isCurrentPeriod = $currentDate->between($startDate, $endDate);
                $accountSummary[] = [
                    'period' => $period['label'],
                    'period_value' => $period['value'],
                    'balance_bf' => $balanceBf,
                    'total_amount' => $totalAmountDue,
                    'payment_amount' => $paidAmount,
                    'payment_date' => $formattedPaymentDate,
                    'balance_carried' => $balanceCarried,
                    'is_overdue' => $isOverdue,
                    'is_current_period' => $isCurrentPeriod,
                    'payments' => $paymentDetails,
                ];
            }

            $overdueAmount = 0;
            $overdueStartDate = null;
            $overdueEndDate = null;
            foreach ($accountSummary as $summary) {
                if ($summary['is_overdue']) {
                    $overdueAmount += $summary['balance_carried'];
                    $dates = explode(' to ', $summary['period_value']);
                    if ($overdueStartDate === null) $overdueStartDate = Carbon::parse($dates[0]);
                    $overdueEndDate = Carbon::parse($dates[1]);
                }
            }

            $estimatedCost = null;
            try {
                $estimatedBilling = BillingPeriod::where('meter_id', $meter->id)
                    ->where('status', 'Estimated')
                    ->latest()
                    ->first();



                $estimatedCost = $estimatedBilling ? $estimatedBilling->cost : 0;
            } catch (\Exception $e) {
                Log::warning('Failed to fetch estimated cost: ' . $e->getMessage());
                $estimatedCost = 0;
            }

            $user = Auth::user()->load('account.property');


            return response()->json([
                'status' => true,
                'data' => $accountSummary,
                'user' => $user,
                'estimatedCost' => $estimatedCost
            ]);
        } catch (\Exception $e) {

            Log::error('Error in getAccountSummary: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'code' => 500,
                'msg' => 'An unexpected error occurred while processing your request.'
            ], 500);
        }
    }

    public function costCalculation(Request $request)
    {

        $meter_id = $request->input('meter_id');
        $this->showDetail($meter_id);
        $meter = Meter::find($meter_id);
        if (!$meter) {
            Log::info("Meter not found for ID: $meter_id");
            return response()->json(['error' => 'Meter not found'], 404);
        }
        $account = $meter->account;
        if (!$account) {
            Log::info("Account not found for meter ID: $meter_id");
            return response()->json(['error' => 'Account not found'], 404);
        }

        $account = $meter->account;
        $property = $account->property;
        $billingDay = $property->billing_day;
        $currentDate = now();


        $billingDate = Carbon::create($currentDate->year, $currentDate->month, $billingDay);

        $diff = $currentDate->diffInDays($billingDate);
        $currentPeriodRemainingReadingDays = $diff;


        if ($currentDate->day >= $billingDay) {
            $currentCycleEnd = $currentDate->copy()->addMonth()->day($billingDay)->subDay();
        } else {
            $currentCycleEnd = $currentDate->copy()->day($billingDay)->subDay();
        }

        $billingPeriods = [];
        for ($i = 0; $i < 4; $i++) {
            $endDate = $currentCycleEnd->copy()->subMonths($i);

            $startDate = $endDate->copy()->subMonth()->addDay();

            $billingPeriods[] = [
                'start_date' => $startDate->copy(),
                'end_date' => $endDate->copy(),
                'label' => $startDate->format('d M') . ' to ' . $endDate->format('d M'),
                'value' => $startDate->toDateString() . ' to ' . $endDate->toDateString(),
            ];
        }
        $billingPeriods = array_reverse($billingPeriods);


        $billings = BillingPeriod::where('meter_id', $meter->id)->orderBy('start_date')->get();
        $payments = Payment::where('meter_id', $meter->id)->orderBy('payment_date')->get();

        $accountSummary = [];
        foreach ($billingPeriods as $index => $period) {
            $startDate = $period['start_date'];
            $endDate = $period['end_date'];

            $periodBillings = $billings->filter(function ($billing) use ($startDate, $endDate) {
                return $billing->start_date <= $endDate && $billing->end_date >= $startDate;
            });

            $periodPayments = $payments->filter(function ($payment) use ($periodBillings) {
                return $periodBillings->pluck('id')->contains($payment->billing_period_id);
            });

            $totalPaymentAmount = 0;
            $paidAmount = 0;
            $latestPaymentDate = null;
            $paymentDetails = [];
            foreach ($periodPayments as $payment) {
                $totalPaymentAmount += (float) $payment->amount;
                if ($payment->status === 'paid') {
                    $paidAmount += (float) $payment->amount;
                    $latestPaymentDate = $payment->payment_date > $latestPaymentDate ? $payment->payment_date : $latestPaymentDate;
                } elseif ($payment->status === 'partially_paid') {
                    $paidAmount += (float) ($payment->total_paid_amount ?? 0);
                    $latestPaymentDate = $payment->payment_date > $latestPaymentDate ? $payment->payment_date : $latestPaymentDate;
                }
                $paymentDetails[] = [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'paid_amount' => $payment->total_paid_amount ?? 0,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('d M Y') : null,
                    'billing_period_id' => $payment->billing_period_id,
                ];
            }

            $balanceBf = $index === 0 ? 0 : $accountSummary[$index - 1]['balance_carried'];
            $totalAmountDue = $totalPaymentAmount + $balanceBf;
            $balanceCarried = $totalAmountDue - $paidAmount;

            if ($balanceCarried < 0) {
                $balanceCarried = 0;
            }

            $formattedPaymentDate = $latestPaymentDate ? Carbon::parse($latestPaymentDate)->format('d M Y') : null;
            $isOverdue = $balanceCarried > 0 && $endDate < $currentDate;
            $isCurrentPeriod = $currentDate->between($startDate, $endDate);
            $accountSummary[] = [
                'period' => $period['label'],
                'period_value' => $period['value'],
                'balance_bf' => $balanceBf,
                'total_amount' => $totalAmountDue,
                'payment_amount' => $paidAmount,
                'payment_date' => $formattedPaymentDate,
                'balance_carried' => $balanceCarried,
                'is_overdue' => $isOverdue,
                'is_current_period' => $isCurrentPeriod,
                'payments' => $paymentDetails,
            ];
        }

        $overdueAmount = 0;
        $overdueStartDate = null;
        $overdueEndDate = null;
        foreach ($accountSummary as $summary) {
            if ($summary['is_overdue']) {
                $overdueAmount += $summary['balance_carried'];
                $dates = explode(' to ', $summary['period_value']);
                if ($overdueStartDate === null) $overdueStartDate = Carbon::parse($dates[0]);
                $overdueEndDate = Carbon::parse($dates[1]);
            }
        }

        $balanceBf = end($accountSummary)['balance_bf'];
        $balanceCd = end($accountSummary)['balance_carried'];

        //current month cost calculation 
        if (!$meter) {
            return response()->json(['status' => false, 'code' => 404, 'msg' => 'Meter not found!']);
        }

        $latestMeterReading = MeterReadings::where('meter_id', $meter_id)->orderBy('reading_date', 'desc')->first();

        if (!$latestMeterReading) {
            return response()->json(['status' => false, 'code' => 404, 'msg' => 'Meter reading not found!']);
        }

        $property = $meter->account->property;
        $estimatedCost = 0;
        $estimatedBilling = null;

        try {
            $estimatedBilling = BillingPeriod::where('meter_id', $meter->id)
                ->where('status', 'Estimated')
                ->latest()
                ->first();


            $periodStartDate = $estimatedBilling->start_date;

            if ($estimatedBilling) {
                $estimatedCost = $estimatedBilling->cost;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch estimated cost2: ' . $e->getMessage());
        }


        $cycleDates = getCurrentMonthCycle($property->billing_day);
        $currentPeriodStart = $cycleDates['start_date'];
        $currentPeriodEnd = $cycleDates['end_date'];


        $estimatedBillingInterim = BillingPeriod::where('meter_id', $meter->id)
            ->whereIn('status', ['Estimated', 'Actual'])
            ->where(function ($query) use ($currentPeriodStart, $currentPeriodEnd) {
                $query->where(function ($q) use ($currentPeriodStart, $currentPeriodEnd) {
                    $q->where('start_date', '<=', $currentPeriodEnd)
                        ->where('end_date', '>=', $currentPeriodStart);
                });
            })
            ->get();


        $latestActualBillingPeriod = $estimatedBillingInterim->where('status', 'Actual')
            ->sortByDesc('end_date')
            ->first();

        $estimatedBillingInterimDailyUsage = $latestActualBillingPeriod ? $latestActualBillingPeriod->daily_usage : null;
        $estimatedBillingInterimDailyCost = $latestActualBillingPeriod ? $latestActualBillingPeriod->daily_cost : null;

        $estimatedBillingInterimTotalUsage = $estimatedBillingInterim->sum('usage_liters');


        $payments = Payment::whereIn('billing_period_id', $estimatedBillingInterim->pluck('id'))
            ->where('status', 'paid')
            ->orderBy('payment_date')
            ->get();
        $totalPaid = $payments->sum('amount');

        $partialPayments = Payment::whereIn('billing_period_id', $estimatedBillingInterim->pluck('id'))
            ->where('status', 'partial_paid')
            ->orderBy('payment_date')
            ->get();
        $totalPartialPaid = $partialPayments->sum('total_paid_amount');
        $estimatedBillingInterimCreditBalance = $totalPaid + $totalPartialPaid;


        $estimatedBillingInterimCostSum = $estimatedBillingInterim->sum('cost');
        $estimatedBillingInterimConsumptionChargeSum = $estimatedBillingInterim->sum('consumption_charge');
        $estimatedBillingInterimDischargeCharges = $estimatedBillingInterim->sum('discharge_charge');

        $estimatedBillingInterimInfrastructureSurcharge = $estimatedBillingInterim->sum(function ($item) {
            return collect($item['additional_costs'])
                ->where('title', 'Infrastructure Surcharge')
                ->sum('cost');
        });

        $estimatedBillingInterimSewageDisposal = $estimatedBillingInterim->sum(function ($item) {
            return collect($item['water_out_additional'])
                ->where('title', 'Sewage Disposal')
                ->sum('cost');
        });



        $estimatedBillingInterimVat = $estimatedBillingInterim->sum('vat');

        $estimatedBillingInterimBalance = $estimatedBillingInterimCostSum - $estimatedBillingInterimCreditBalance;


        $estimatedBillingInterimFinal = [
            'cycleDates' => $cycleDates,
            'total_amount' => $estimatedBillingInterimCostSum,
            'consumption_charge' => $estimatedBillingInterimConsumptionChargeSum,
            'discharge_charges' => $estimatedBillingInterimDischargeCharges,
            'infrastructure_surcharge' => $estimatedBillingInterimInfrastructureSurcharge,
            'sewage_disposal' => $estimatedBillingInterimSewageDisposal,
            'vat' => $estimatedBillingInterimVat,
            'balance' => $estimatedBillingInterimBalance,
            'estimated_billing_interim_credit_balance' => $estimatedBillingInterimCreditBalance,
            'estimated_billing_interim_total_usage' => $estimatedBillingInterimTotalUsage,
            'estimated_billing_interim_daily_usage' => $estimatedBillingInterimDailyUsage,
            'estimated_billing_interim_daily_cost' => $estimatedBillingInterimDailyCost,
        ];


        $billingPeriods = BillingPeriod::where('meter_id', $meter->id)
            ->where('status', '=', 'Actual')
            ->where(function ($query) use ($currentPeriodStart, $currentPeriodEnd) {
                $query->where(function ($q) use ($currentPeriodStart, $currentPeriodEnd) {
                    $q->where('start_date', '<=', $currentPeriodEnd)
                        ->where('end_date', '>=', $currentPeriodStart);
                });
            })
            ->get();

        $cost = 0;
        foreach ($billingPeriods as $billing) {
            $cost += $billing->cost;
        }



        $payments = Payment::whereIn('billing_period_id', $billingPeriods->pluck('id'))
            ->where('status', 'paid')
            ->orderBy('payment_date')
            ->get();


        $paymentDetails = $payments->map(function ($payment) {
            return [
                'total_paid_amount' => $payment->total_paid_amount,
                'payment_date' => $payment->payment_date,
            ];
        })->values()->toArray();


        $totalAmount = collect($paymentDetails)->sum('total_paid_amount');

        $consumptionChargeSum = $billingPeriods->sum('consumption_charge');
        $dischargeCharges = $billingPeriods->sum('discharge_charge');

        $infrastructureSurchargeSum = $billingPeriods->sum(function ($billing) {
            $additionalCosts = $billing->additional_costs ?? [];
            foreach ($additionalCosts as $cost) {
                if ($cost['title'] === 'Infrastructure Surcharge') {
                    return (float) $cost['cost'];
                }
            }
            return 0;
        });

        $sewageDisposalSum = $billingPeriods->sum(function ($billing) {
            $waterOutAdditional = $billing->water_out_additional ?? [];
            foreach ($waterOutAdditional as $cost) {
                if ($cost['title'] === 'Sewage Disposal') {
                    return (float) $cost['cost'];
                }
            }
            return 0;
        });

        $vatSum = $billingPeriods->sum('vat');


        $currentBill = [

            'balance_bf' => $balanceCarried,
            'consumption_charge' => $consumptionChargeSum,
            'infrastructure_surcharge' => $infrastructureSurchargeSum,
            'sewage_disposal' => $sewageDisposalSum,
            'vat' => $vatSum,
            'discharge_charges' => $dischargeCharges,
            'total' => $cost + $balanceCarried,
            'balanceCd' => $balanceCd,
            'current_month_payment_details' => $paymentDetails,
        ];





        $usage_liters = $billingPeriods->sum('usage_liters');



        // Part 1: Generate $accountSummary
        $accountSummary = [];
        foreach ($billingPeriods as $index => $period) {
            $startDate = $period['start_date'];
            $endDate = $period['end_date'];

            $periodBillings = $billings->filter(function ($billing) use ($startDate, $endDate) {
                return $billing->start_date <= $endDate && $billing->end_date >= $startDate;
            });

            $periodPayments = $payments->filter(function ($payment) use ($periodBillings) {
                return $periodBillings->pluck('id')->contains($payment->billing_period_id);
            });

            $totalPaymentAmount = 0;
            $paidAmount = 0;
            $latestPaymentDate = null;
            $paymentDetails = [];
            foreach ($periodPayments as $payment) {
                $totalPaymentAmount += (float) $payment->amount;
                if ($payment->status === 'paid') {
                    $paidAmount += (float) $payment->amount;
                    $latestPaymentDate = $payment->payment_date > $latestPaymentDate ? $payment->payment_date : $latestPaymentDate;
                } elseif ($payment->status === 'partially_paid') {
                    $paidAmount += (float) ($payment->total_paid_amount ?? 0);
                    $latestPaymentDate = $payment->payment_date > $latestPaymentDate ? $payment->payment_date : $latestPaymentDate;
                }
                $paymentDetails[] = [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'paid_amount' => $payment->total_paid_amount ?? 0,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('d M Y') : null,
                    'billing_period_id' => $payment->billing_period_id,
                ];
            }

            $balanceBf = $index === 0 ? 0 : $accountSummary[$index - 1]['balance_carried'];
            $totalAmountDue = $totalPaymentAmount + $balanceBf;
            $balanceCarried = $totalAmountDue - $paidAmount;

            if ($balanceCarried < 0) {
                $balanceCarried = 0;
            }

            $formattedPaymentDate = $latestPaymentDate ? Carbon::parse($latestPaymentDate)->format('d M Y') : null;
            $currentDate = Carbon::today();
            $isOverdue = $balanceCarried > 0 && $endDate < $currentDate;
            $isCurrentPeriod = $currentDate->between($startDate, $endDate);
            $accountSummary[] = [
                'period' => $period['label'],
                'period_value' => $period['value'],
                'balance_bf' => $balanceBf,
                'total_amount' => $totalAmountDue,
                'payment_amount' => $paidAmount,
                'payment_date' => $formattedPaymentDate,
                'balance_carried' => $balanceCarried,
                'is_overdue' => $isOverdue,
                'is_current_period' => $isCurrentPeriod,
                'payments' => $paymentDetails,
            ];
        }

        // Part 2: Get Balance Carried for Previous Cycle and Update $perviousBill
        $perviousCycleDates = getPerviousMonthCycle($property->billing_day);
        $meterReadings = MeterReadings::where('meter_id', $meter->id)
            ->whereBetween('reading_date', [
                $perviousCycleDates['previous_start_date'],
                Carbon::parse($perviousCycleDates['previous_end_date'])->endOfDay() // Ensure we get all readings on 23rd
            ])
            ->get();

        $latestReading = $meterReadings->sortByDesc('reading_date')->first();
        $perviousMonthLatestReadingDate = Carbon::parse($latestReading?->reading_date)
            ->format('d M Y');
        // Find the matching period in $accountSummary
        $previousPeriodSummary = collect($accountSummary)->firstWhere('period_value', "{$perviousCycleDates['previous_start_date']} to {$perviousCycleDates['previous_end_date']}");
        $balanceCarriedFromSummary = $previousPeriodSummary ? $previousPeriodSummary['balance_carried'] : 0;

        // Fetch previous billing periods
        $perviousBillingPeriods = BillingPeriod::where('meter_id', $meter->id)
            ->where('status', '=', 'Final Estimate')
            ->where(function ($query) use ($perviousCycleDates) {
                $query->where(function ($q) use ($perviousCycleDates) {
                    $q->where('start_date', '<=', $perviousCycleDates['previous_end_date'])
                        ->where('end_date', '>=', $perviousCycleDates['previous_start_date']);
                });
            })
            ->get();

        $latestBillingPeriod = $perviousBillingPeriods->sortByDesc('end_date')->first();
        $perviousMonthDailyUsage = $latestBillingPeriod->daily_usage ?? 0;
        $perviousMonthDailyCost = $latestBillingPeriod->daily_cost ?? 0;

        //now get total usages from perviousBillingPeriods
        $perviousMonthTotalUsage = $perviousBillingPeriods->sum('usage_liters');



        // Get unpaid amount for previous cycle
        $perviousPayments = Payment::whereIn('billing_period_id', $perviousBillingPeriods->pluck('id'))
            ->whereIn('status', ['pending', 'partially_paid'])
            ->orderBy('payment_date')
            ->get();

        // Calculate outstanding amount
        $partiallyPaidTotal = $perviousPayments->where('status', 'partially_paid')
            ->sum(fn($payment) => $payment->amount - $payment->total_paid_amount);
        $pendingTotal = $perviousPayments->where('status', 'pending')->sum('amount');
        $totalOutstandingAmount = $partiallyPaidTotal + $pendingTotal;

        // Get total cost for previous cycles
        $cost = $perviousBillingPeriods->sum('cost');
        $month_total_cost = $cost - $totalOutstandingAmount;

        // Get paid amount for previous cycles
        $payments = Payment::whereIn('billing_period_id', $perviousBillingPeriods->pluck('id'))
            ->where('status', 'paid')
            ->orderBy('payment_date')
            ->get();

        // Get total_paid_amount from payments
        $paymentDetails = $payments->map(fn($payment) => [
            'total_paid_amount' => (float) $payment->total_paid_amount,
            'payment_date' => $payment->payment_date,
        ])->values()->toArray();
        $totalAmount = collect($paymentDetails)->sum('total_paid_amount');

        // Calculate balanceCd for this period
        $balanceCd = ($cost + $balanceCarriedFromSummary) - $totalAmount;

        // Calculate additional sums
        $consumptionChargeSum = $perviousBillingPeriods->sum('consumption_charge');
        $dischargeCharges = $perviousBillingPeriods->sum('discharge_charge');
        $infrastructureSurchargeSum = $perviousBillingPeriods->flatMap(fn($billing) => $billing->additional_costs ?? [])
            ->where('title', 'Infrastructure Surcharge')
            ->sum('cost');
        $sewageDisposalSum = $perviousBillingPeriods->flatMap(fn($billing) => $billing->water_out_additional ?? [])
            ->where('title', 'Sewage Disposal')
            ->sum('cost');
        $vatSum = $perviousBillingPeriods->sum('vat');




        $perviousBill = [
            'balance_bf' => $balanceCarriedFromSummary,
            'consumption_charge' => $consumptionChargeSum,
            'infrastructure_surcharge' => $infrastructureSurchargeSum,
            'sewage_disposal' => $sewageDisposalSum,
            'vat' => $vatSum,
            'discharge_charges' => $dischargeCharges,
            'total_cost' => $cost,
            'total' => $cost + $balanceCarriedFromSummary,
            'balanceCd' => $balanceCd,
            'month_total_cost' => $month_total_cost,
            'totalOutstandingAmount' => $totalOutstandingAmount,
            'perviousCycleDates' => $perviousCycleDates,
            'perviousMonthLatestReadingDate' => $perviousMonthLatestReadingDate,
            'perviousMonthDailyUsage' => $perviousMonthDailyUsage,
            'perviousMonthDailyCost' => $perviousMonthDailyCost,
            'perviousMonthTotalUsage' => $perviousMonthTotalUsage,

        ];


        //Reading overdue calculation

        $latestPerviousBillingPeriod = $perviousBillingPeriods->last();

        $lastReadingEndDate = Carbon::parse($latestPerviousBillingPeriod?->end_date)->timezone('UTC');

        $currentPeriodStart = Carbon::parse($cycleDates['start_date']);
        $expectedEndDate = $currentPeriodStart->copy()->subDay()->endOfDay();

        if ($lastReadingEndDate->lt($expectedEndDate)) {
            if ($currentDate->gt($expectedEndDate)) {
                $overdueDays = $currentDate->diffInDays($expectedEndDate);
                $overdueDaysMessage = "Reading overdue > {$overdueDays} day" . ($overdueDays !== 1 ? 's' : '');
            } else {
                $overdueDaysMessage = "Reading is not overdue.";
            }
        } else {
            $overdueDaysMessage = "Reading is up to date.";
        }


        $currentBill['overdueDays'] = $overdueDaysMessage;


        $estimatedBillingInterim = [
            'current_period' => $cycleDates,
            'currentPeriodRemainingReadingDays' => $currentPeriodRemainingReadingDays,

        ];

        return response()->json([
            'status' => true,
            'currentPeriodRemainingReadingDays' => $currentPeriodRemainingReadingDays,
            'meter' => $meter,
            'latest_meter_reading' => $latestMeterReading,
            'estimated_bill' => $estimatedBilling ?? '',
            'current_period' => $cycleDates,
            'current_bill' => $currentBill,
            'perviousBill' => $perviousBill,
            'estimated_billing_interim' => $estimatedBillingInterimFinal,


        ]);
    }


    // function for calculation of projected, interim cost and final cost


    public function showDetail($id)
    {

        Log::info('here2');

        $meter = Meter::with(['meterTypes', 'meterCategory', 'readings.addedBy', 'account', 'payments.billingPeriod'])->where('id', $id)->firstOrFail();
        $meterReadings = $meter->readings;

        $account = $meter->account;
        $property = $account->property;
        $propertyManager = $property->property_manager;


        $billingDate = $property->billing_day;

        $currentDate = now();

        // pervious billing cycles
        $perviousBillingCycles = [];
        for ($i = 0; $i < 6; $i++) {
            $endDate = Carbon::createFromDate($currentDate->year, $currentDate->month, $billingDate)
                ->subDay()
                ->subMonths($i);
            $startDate = Carbon::createFromDate($endDate->year, $endDate->month, $billingDate)
                ->subMonth();

            $perviousBillingCycles[] = [
                'start_date' => $startDate->format('d M Y'),
                'end_date' => $endDate->format('d M Y'),
                'value' => $startDate->toDateString() . ' ' . $endDate->toDateString(),
            ];
        }

        $perviousBillingCycles = array_reverse($perviousBillingCycles);


        //billing period
        if ($currentDate->day < $billingDate) {
            $startDate = $currentDate->copy()->subMonth()->setDay($billingDate);
            $endDate = $currentDate->copy()->setDay($billingDate - 1);
        } else {
            $startDate = $currentDate->copy()->setDay($billingDate);
            $endDate = $currentDate->copy()->addMonth()->setDay($billingDate - 1);
        }
        function getOrdinalSuffix($day)
        {
            if (!in_array(($day % 100), [11, 12, 13])) {
                switch ($day % 10) {
                    case 1:
                        return 'st';
                    case 2:
                        return 'nd';
                    case 3:
                        return 'rd';
                }
            }
            return 'th';
        }
        $startDay = $startDate->day . getOrdinalSuffix($startDate->day);
        $endDay = $endDate->day . getOrdinalSuffix($endDate->day);
        $propertyBillingPeriod = "{$startDay} {$startDate->format('M')} to {$endDay} {$endDate->format('M')}";


        //get previous month readings
        $startDate = now()->subMonths(2)->setDay($billingDate)->startOfDay();
        $endDate = now()->subMonth()->setDay($billingDate)->startOfDay();

        $previousMonthReadings = $meterReadings
            ->filter(function ($reading) use ($startDate, $endDate) {

                $readingDate = Carbon::parse($reading->reading_date);

                return $readingDate >= $startDate && $readingDate <= $endDate;
            })
            ->sortBy('reading_date');


        $usage = [];
        $previousValue = null;
        foreach ($previousMonthReadings as $reading) {
            if ($previousValue !== null) {
                $usage[] = (int)$reading->reading_value - (int)$previousValue;
            }
            $previousValue = $reading->reading_value;
        }
        $previousCycleTotalUsage = array_sum($usage);


        $previousMonthLatestReading = $previousMonthReadings->last();


        $payments = $meter->payments;

        $meterType = strtolower($meter->meterTypes->title);
        $cycleDates = getCurrentMonthCycle($property->billing_day);


        $cycleStartDate = Carbon::parse($cycleDates['start_date']);
        $cycleEndDate = Carbon::parse($cycleDates['end_date']);

        // Current billing cycle payments
        $payments = $payments->load('billingPeriod');

        $currentCyclePayments = $payments->filter(function ($payment) use ($cycleStartDate, $cycleEndDate) {
            $billingPeriod = $payment->billingPeriod;
            if (!$billingPeriod) {
                return false;
            }
        
            $billingStart = Carbon::parse($billingPeriod->start_date);
            $billingEnd = Carbon::parse($billingPeriod->end_date);
        
            return $billingStart->between($cycleStartDate, $cycleEndDate) ||
                   $billingEnd->between($cycleStartDate, $cycleEndDate);
        });
        


        $currentPaymentsAmount = $currentCyclePayments->sum('amount');

        // overdue payments
        $pendingPayments = $meter->payments->where('status', 'pending');
        $pendingPaymentsAmount = $pendingPayments->sum('amount');
        $partiallyPaidPayments = $meter->payments->where('status', 'partially_paid');
        $partiallyPaidPaymentsAmount = $partiallyPaidPayments->sum('amount') - $partiallyPaidPayments->sum('total_paid_amount');
        $totalPendingAmount = $pendingPaymentsAmount + $partiallyPaidPaymentsAmount;

        //latest reading fot current billing cycle
        $latestReading = $meterReadings->filter(function ($reading) use ($cycleStartDate) {
            return Carbon::parse( $reading->reading_date)
                ->gte($cycleStartDate);
        })->sortByDesc('reading_date')->first();

        //get all readings of current billing cycle
        $currentCycleReadings = $meterReadings->filter(function ($reading) use ($cycleStartDate, $cycleEndDate) {
            $readingDate = Carbon::parse($reading->reading_date);
            return $readingDate >= $cycleStartDate && $readingDate <= $cycleEndDate;
        });


        $totalReadingDifference = 0;
        if ($latestReading && $previousMonthLatestReading) {
            $latestReadingValue = (int) ltrim($latestReading->reading_value, '0');
            $previousMonthLatestReadingValue = (int) ltrim($previousMonthLatestReading->reading_value, '0');
            $totalReadingDifference = $latestReadingValue - $previousMonthLatestReadingValue;
        }


        $meterId = $meter->id;
        $includeVAT = true;


        $waterInCost = json_decode($meter->account->property->cost->water_in, true) ?: [];
        $waterOutCost = json_decode($meter->account->property->cost->water_out, true) ?: [];
        $electricityCost = json_decode($meter->account->property->cost->electricity, true) ?: [];
        $waterInAdditional = json_decode($meter->account->property->cost->waterin_additional ?? '[]', true) ?: [];
        $waterOutAdditional = json_decode($meter->account->property->cost->waterout_additional ?? '[]', true) ?: [];
        $electricityAdditional = json_decode($meter->account->property->cost->electricity_additional ?? '[]', true) ?: [];
        $vatPercentage = (float) ($meter->account->property->cost->vat_percentage ?? 0);

        $billingPeriods = $this->calculateBillingPeriods(
            $meterId,
            $meterType,
            $sortedReadings = $meter->readings->sortBy('reading_date'),
            $cycleStartDate,
            $cycleEndDate,
            $currentDate,
            $includeVAT,
            $waterInCost,
            $waterOutCost,
            $electricityCost,
            $waterInAdditional,
            $waterOutAdditional,
            $electricityAdditional,
            $vatPercentage,
            $account->id
        );

    
    }

    /**
     * Calculate billing periods and sync them to the database, generating payments for actual and final estimate periods.
     *
     * @return array
     */
    protected function calculateBillingPeriods(
        $meterId,
        $meterType,
        $sortedReadings,
        $cycleStartDate,
        $cycleEndDate,
        $currentDate,
        $includeVAT,
        $waterInCost,
        $waterOutCost,
        $electricityCost,
        $waterInAdditional,
        $waterOutAdditional,
        $electricityAdditional,
        $vatPercentage,
        $accountId
    ) {

        Log::info('here3');
        $billingPeriods = [];
        $prevReading = null;
    
        $sortedReadings = $sortedReadings->sortBy('reading_date');
    
        $getCost = function ($usage, $costArray) {
            $totalCost = 0;
            $remainingUsage = $usage;
            foreach ($costArray as $tier) {
                $tierMin = (int) $tier['min'];
                $tierMax = (int) $tier['max'];
                $tierCost = (float) $tier['cost'];
                if ($remainingUsage > 0) {
                    $tierUsage = min($remainingUsage, $tierMax - $tierMin);
                    $totalCost += $tierUsage * $tierCost;
                    $remainingUsage -= $tierUsage;
                    if ($remainingUsage <= 0) break;
                }
            }
            return $totalCost;
        };
    
        $getAdditionalCosts = function ($additionalCosts, $usage) {
            $finalAdditionalCosts = [];
            $total = 0;
            foreach ($additionalCosts as $additionalCost) {
                $percentage = isset($additionalCost['percentage']) && $additionalCost['percentage'] > 0 ? (float)$additionalCost['percentage'] : 100;
                $adjustedUsage = $percentage == 100 ? $usage : $usage * ($percentage / 100);
                if (isset($additionalCost['percentage']) && empty($additionalCost['percentage'])) $adjustedUsage = 1;
                $cost = round($adjustedUsage * (float)$additionalCost['cost'], 2);
                $finalAdditionalCosts[] = [
                    'title' => $additionalCost['title'],
                    'cost' => $cost,
                ];
                $total += $cost;
            }
            return ['additional_costs' => $finalAdditionalCosts, 'total' => round($total, 2)];
        };
    
        $isCurrentCycle = $currentDate->between($cycleStartDate, $cycleEndDate);
    
    
        BillingPeriod::where('meter_id', $meterId)
            ->whereIn('status', ['Final Estimate'])
            ->where('end_date', '<=', $cycleStartDate->toDateString())
            ->where('end_reading_id', null)
            ->delete();
    
        foreach ($sortedReadings as $reading) {
            $readingDate = Carbon::parse( $reading->reading_date);
            $readingValue = (int) ltrim($reading->reading_value, '0');
            $rawReadingValue = $reading->reading_value;
            $readingId = $reading->id;
    
            if ($prevReading) {
                $prevDate = Carbon::parse($prevReading->reading_date);
                $prevValue = (int) ltrim($prevReading->reading_value, '0');
                $rawPrevValue = $prevReading->reading_value;
                $prevReadingId = $prevReading->id;
                $usage = max(0, $readingValue - $prevValue);
                $daysInPeriod = $prevDate->diffInDays($readingDate);
    
                if ($prevDate->lte($cycleEndDate)) {
                    $effectiveEndDate = $readingDate->gt($cycleEndDate) ? $cycleEndDate : $readingDate;
    
                    if ($meterType === 'water') {
                        $consumptionCharge = $getCost($usage, $waterInCost);
                        $dischargeCharge = $getCost($usage, $waterOutCost);
                        $waterInAdditionalCost = $getAdditionalCosts($waterInAdditional, $usage);
                        $waterOutAdditionalCost = $getAdditionalCosts($waterOutAdditional, $usage);
                        $baseCost = $consumptionCharge + $dischargeCharge + $waterInAdditionalCost['total'] + $waterOutAdditionalCost['total'];
                        $vat = ($vatPercentage / 100) * $baseCost;
                        $totalCost = $includeVAT ? $baseCost + $vat : $baseCost;
                    } elseif ($meterType === 'electricity') {
                        $consumptionCharge = $getCost($usage, $electricityCost);
                        $dischargeCharge = 0;
                        $electricityAdditionalCost = $getAdditionalCosts($electricityAdditional, $usage);
                        $baseCost = $consumptionCharge + $electricityAdditionalCost['total'];
                        $vat = ($vatPercentage / 100) * $baseCost;
                        $totalCost = $includeVAT ? $baseCost + $vat : $baseCost;
                    } else {
                        $consumptionCharge = 0;
                        $dischargeCharge = 0;
                        $electricityAdditionalCost = ['additional_costs' => [], 'total' => 0];
                        $baseCost = 0;
                        $vat = 0;
                        $totalCost = 0;
                    }
    
                    $dailyUsage = $daysInPeriod > 0 ? $usage / $daysInPeriod : 0;
                    $dailyCost = $daysInPeriod > 0 ? $totalCost / $daysInPeriod : 0;
    
                    $status = $prevDate->gte($cycleStartDate) && $effectiveEndDate->lte($cycleEndDate) ? "Actual" : "Final Estimate";
                    if ($daysInPeriod <= 1) {
                        if ($effectiveEndDate->lte($cycleStartDate)) {
                            $status = "Final Estimate";
                        } else {
                            continue;
                        }
                    }
    
                    if ($prevDate->lt($cycleStartDate) && $readingDate->gt($cycleStartDate)) {
                        $daysBeforeCycle = $prevDate->diffInDays($cycleStartDate);
                        $daysInCycle = $cycleStartDate->diffInDays($readingDate); // Define $daysInCycle here
                        $totalDays = $prevDate->diffInDays($readingDate);
                        $usageBeforeCycle = $daysBeforeCycle > 0 ? ($usage * $daysBeforeCycle / $totalDays) : 0;
                        $usageInCycle = $usage - $usageBeforeCycle;
                        $splitReadingValue = $prevValue + $usageBeforeCycle;
                        $rawSplitReadingValue = str_pad((int)$splitReadingValue, 6, '0', STR_PAD_LEFT);
    
                        if ($usageBeforeCycle > 0 && $daysBeforeCycle > 1) {
                            if ($meterType === 'water') {
                                $beforeConsumptionCharge = $getCost($usageBeforeCycle, $waterInCost);
                                $beforeDischargeCharge = $getCost($usageBeforeCycle, $waterOutCost);
                                $beforeWaterInAdditional = $getAdditionalCosts($waterInAdditional, $usageBeforeCycle);
                                $beforeWaterOutAdditional = $getAdditionalCosts($waterOutAdditional, $usageBeforeCycle);
                                $beforeBaseCost = $beforeConsumptionCharge + $beforeDischargeCharge + $beforeWaterInAdditional['total'] + $beforeWaterOutAdditional['total'];
                            } elseif ($meterType === 'electricity') {
                                $beforeConsumptionCharge = $getCost($usageBeforeCycle, $electricityCost);
                                $beforeDischargeCharge = 0;
                                $beforeElectricityAdditional = $getAdditionalCosts($electricityAdditional, $usageBeforeCycle);
                                $beforeBaseCost = $beforeConsumptionCharge + $beforeElectricityAdditional['total'];
                            } else {
                                $beforeBaseCost = 0;
                            }
                            $beforeVat = ($vatPercentage / 100) * $beforeBaseCost;
                            $beforeTotalCost = $includeVAT ? $beforeBaseCost + $beforeVat : $beforeBaseCost;
                            $beforeDailyUsage = $daysBeforeCycle > 0 ? $usageBeforeCycle / $daysBeforeCycle : 0;
                            $beforeDailyCost = $daysBeforeCycle > 0 ? $beforeTotalCost / $daysBeforeCycle : 0;
    
                            $billingPeriods[] = [
                                'meter_id' => $meterId,
                                'start_date' => $prevDate->toDateString(),
                                'end_date' => $cycleStartDate->toDateString(),
                                'start_reading' => $rawPrevValue,
                                'end_reading' => $rawSplitReadingValue,
                                'start_reading_id' => $prevReadingId,
                                'end_reading_id' => null,
                                'usage_liters' => round($usageBeforeCycle, 2),
                                'cost' => $beforeTotalCost,
                                'consumption_charge' => $beforeConsumptionCharge,
                                'discharge_charge' => $beforeDischargeCharge,
                                'additional_costs' => $meterType === 'water' ? $beforeWaterInAdditional['additional_costs'] : ($meterType === 'electricity' ? $beforeElectricityAdditional['additional_costs'] : []),
                                'water_out_additional' => $meterType === 'water' ? $beforeWaterOutAdditional['additional_costs'] : [],
                                'vat' => round($beforeVat, 2),
                                'daily_usage' => round($beforeDailyUsage, 2),
                                'daily_cost' => round($beforeDailyCost, 2),
                                'status' => "Final Estimate"
                            ];
                        }
    
                        if ($usageInCycle > 0) {
                            if ($meterType === 'water') {
                                $inCycleConsumptionCharge = $getCost($usageInCycle, $waterInCost);
                                $inCycleDischargeCharge = $getCost($usageInCycle, $waterOutCost);
                                $inCycleWaterInAdditional = $getAdditionalCosts($waterInAdditional, $usageInCycle);
                                $inCycleWaterOutAdditional = $getAdditionalCosts($waterOutAdditional, $usageInCycle);
                                $inCycleBaseCost = $inCycleConsumptionCharge + $inCycleDischargeCharge + $inCycleWaterInAdditional['total'] + $inCycleWaterOutAdditional['total'];
                            } elseif ($meterType === 'electricity') {
                                $inCycleConsumptionCharge = $getCost($usageInCycle, $electricityCost);
                                $inCycleDischargeCharge = 0;
                                $inCycleElectricityAdditional = $getAdditionalCosts($electricityAdditional, $usageInCycle);
                                $inCycleBaseCost = $inCycleConsumptionCharge + $inCycleElectricityAdditional['total'];
                            } else {
                                $inCycleBaseCost = 0;
                            }
                            $inCycleVat = ($vatPercentage / 100) * $inCycleBaseCost;
                            $inCycleTotalCost = $includeVAT ? $inCycleBaseCost + $inCycleVat : $inCycleBaseCost;
                            $inCycleDailyUsage = $daysInCycle > 0 ? $usageInCycle / $daysInCycle : 0;
                            $inCycleDailyCost = $daysInCycle > 0 ? $inCycleTotalCost / $daysInCycle : 0;
    
                            $billingPeriods[] = [
                                'meter_id' => $meterId,
                                'start_date' => $cycleStartDate->toDateString(),
                                'end_date' => $readingDate->toDateString(),
                                'start_reading' => $rawPrevValue, // Use actual previous reading
                                'end_reading' => $rawReadingValue,
                                'start_reading_id' => $prevReadingId,
                                'end_reading_id' => $readingId,
                                'usage_liters' => $usage, // Use full actual usage
                                'cost' => $totalCost, // Use full cost based on actual readings
                                'consumption_charge' => $consumptionCharge,
                                'discharge_charge' => $dischargeCharge,
                                'additional_costs' => $meterType === 'water' ? $waterInAdditionalCost['additional_costs'] : ($meterType === 'electricity' ? $electricityAdditionalCost['additional_costs'] : []),
                                'water_out_additional' => $meterType === 'water' ? $waterOutAdditionalCost['additional_costs'] : [],
                                'vat' => round($vat, 2),
                                'daily_usage' => round($dailyUsage, 2),
                                'daily_cost' => round($dailyCost, 2),
                                'status' => "Actual"
                            ];
                        }
                    } else {
                        $billingPeriods[] = [
                            'meter_id' => $meterId,
                            'start_date' => $prevDate->toDateString(),
                            'end_date' => $effectiveEndDate->toDateString(),
                            'start_reading' => $rawPrevValue,
                            'end_reading' => $rawReadingValue,
                            'start_reading_id' => $prevReadingId,
                            'end_reading_id' => $readingId,
                            'usage_liters' => $usage,
                            'cost' => $totalCost,
                            'consumption_charge' => $consumptionCharge,
                            'discharge_charge' => $dischargeCharge,
                            'additional_costs' => $meterType === 'water' ? $waterInAdditionalCost['additional_costs'] : ($meterType === 'electricity' ? $electricityAdditionalCost['additional_costs'] : []),
                            'water_out_additional' => $meterType === 'water' ? $waterOutAdditionalCost['additional_costs'] : [],
                            'vat' => round($vat, 2),
                            'daily_usage' => round($dailyUsage, 2),
                            'daily_cost' => round($dailyCost, 2),
                            'status' => $status
                        ];
                    }
                }
            }
            $prevReading = $reading;
        }
    
    
        $lastReadingDate = $sortedReadings->isEmpty() 
            ? $cycleStartDate 
            : Carbon::parse($sortedReadings->last()->reading_date);
        $lastReadingValue = $sortedReadings->isEmpty() ? 0 : (int) ltrim($sortedReadings->last()->reading_value, '0');
        $rawLastReadingValue = $sortedReadings->isEmpty() ? '000000' : $sortedReadings->last()->reading_value;
        $lastReadingId = $sortedReadings->isEmpty() ? null : $sortedReadings->last()->id;
        $prevLastReading = $sortedReadings->count() > 1 ? $sortedReadings->slice(-2, 1)->first() : null;
    
        if ($isCurrentCycle && $lastReadingDate->lt($cycleEndDate)) {
            $daysRemaining = $cycleEndDate->diffInDays($lastReadingDate);
            if ($daysRemaining > 1) {
                $prevDate = $prevLastReading ? Carbon::parse( $prevLastReading->reading_date) : $cycleStartDate;
                $daysSoFar = $prevDate->diffInDays($lastReadingDate);
                $lastPeriodUsage = $prevLastReading ? ($lastReadingValue - (int) ltrim($prevLastReading->reading_value, '0')) : 0;
                $dailyUsageRate = $daysSoFar > 0 ? $lastPeriodUsage / $daysSoFar : 0;
                $estimatedUsage = $dailyUsageRate * $daysRemaining;
                $estimatedEndReading = $lastReadingValue + $estimatedUsage;
                $rawEstimatedEndReading = str_pad((int)$estimatedEndReading, 6, '0', STR_PAD_LEFT);
    
                if ($meterType === 'water') {
                    $estimatedConsumptionCharge = $getCost($estimatedUsage, $waterInCost);
                    $estimatedDischargeCharge = $getCost($estimatedUsage, $waterOutCost);
                    $estimatedWaterInAdditional = $getAdditionalCosts($waterInAdditional, $estimatedUsage);
                    $estimatedWaterOutAdditional = $getAdditionalCosts($waterOutAdditional, $estimatedUsage);
                    $estimatedBaseCost = $estimatedConsumptionCharge + $estimatedDischargeCharge + $estimatedWaterInAdditional['total'] + $estimatedWaterOutAdditional['total'];
                    $estimatedVat = ($vatPercentage / 100) * $estimatedBaseCost;
                    $estimatedCost = $includeVAT ? $estimatedBaseCost + $estimatedVat : $estimatedBaseCost;
                } elseif ($meterType === 'electricity') {
                    $estimatedConsumptionCharge = $getCost($estimatedUsage, $electricityCost);
                    $estimatedDischargeCharge = 0;
                    $electricityAdditionalCost = $getAdditionalCosts($electricityAdditional, $usage);
                    $estimatedBaseCost = $estimatedConsumptionCharge + $electricityAdditionalCost['total'];
                    $estimatedVat = ($vatPercentage / 100) * $estimatedBaseCost;
                    $estimatedCost = $includeVAT ? $estimatedBaseCost + $estimatedVat : $estimatedBaseCost;
                } else {
                    $estimatedCost = 0;
                    $estimatedConsumptionCharge = 0;
                    $estimatedDischargeCharge = 0;
                    $estimatedElectricityAdditional = ['additional_costs' => [], 'total' => 0];
                    $estimatedVat = 0;
                }
    
                $estimatedDailyUsage = $daysRemaining > 0 ? $estimatedUsage / $daysRemaining : 0;
                $estimatedDailyCost = $daysRemaining > 0 ? $estimatedCost / $daysRemaining : 0;
    
    
                $billingPeriods[] = [
                    'meter_id' => $meterId,
                    'start_date' => $lastReadingDate->toDateString(),
                    'end_date' => $cycleEndDate->toDateString(),
                    'start_reading' => $rawLastReadingValue,
                    'end_reading' => $rawEstimatedEndReading,
                    'start_reading_id' => $lastReadingId,
                    'end_reading_id' => null,
                    'usage_liters' => round($estimatedUsage, 2),
                    'cost' => $estimatedCost,
                    'consumption_charge' => $estimatedConsumptionCharge,
                    'discharge_charge' => $estimatedDischargeCharge,
                    'additional_costs' => $meterType === 'water' ? $estimatedWaterInAdditional['additional_costs'] : ($meterType === 'electricity' ? $electricityAdditionalCost['additional_costs'] : []),
                    'water_out_additional' => $meterType === 'water' ? $estimatedWaterOutAdditional['additional_costs'] : [],
                    'vat' => round($estimatedVat, 2),
                    'daily_usage' => round($estimatedDailyUsage, 2),
                    'daily_cost' => round($estimatedDailyCost, 2),
                    'status' => "Estimated"
                ];
            }
        }
    
        // Filter out unwanted single-day periods
        $filteredBillingPeriods = [];
        foreach ($billingPeriods as $period) {
            $startDate = Carbon::parse($period['start_date']);
            $endDate = Carbon::parse($period['end_date']);
            if ($period['status'] === 'Final Estimate' && 
                $startDate->diffInDays($endDate) <= 1 && 
                $endDate->toDateString() === $cycleStartDate->toDateString() && 
                $period['end_reading_id'] === null) {
                continue; // Skip adjusting next period since we're using actual readings
            }
            $filteredBillingPeriods[] = $period;
        }
        $billingPeriods = $filteredBillingPeriods;
    
    
        foreach ($billingPeriods as $period) {
            $billingPeriod = BillingPeriod::updateOrCreate(
                [
                    'meter_id' => $meterId,
                    'start_date' => $period['start_date'],
                    'end_date' => $period['end_date'],
                ],
                $period
            );
    
            if (($period['status'] === 'Actual' || $period['status'] === 'Final Estimate') && !is_null($period['end_reading_id'])) {
                $readingDate = Carbon::parse($period['end_date'])->toDateString();
                $existingPayment = Payment::where('meter_id', $meterId)
                    ->where('billing_period_id', $billingPeriod->id)
                    ->where('reading_id', $period['end_reading_id'])
                    ->exists();
    
                if (!$existingPayment) {
                    Payment::create([
                        'account_id' => $accountId,
                        'meter_id' => $meterId,
                        'reading_id' => $period['end_reading_id'],
                        'payment_date' => null,
                        'payment_method' => null,
                        'amount' => $period['cost'],
                        'status' => 'pending',
                        'billing_period_id' => $billingPeriod->id,
                    ]);
                }
            }
        }
    
        $latestReadingTimestamp = $sortedReadings->max('updated_at') ?? Carbon::minValue();
        $lastBillingUpdate = BillingPeriod::where('meter_id', $meterId)->max('updated_at') ?? Carbon::minValue();
        $hasNewReading = $latestReadingTimestamp > $lastBillingUpdate;
    
        if ($hasNewReading) {
            $existingPeriods = BillingPeriod::where('meter_id', $meterId)
                ->where('end_date', '<', $sortedReadings->isEmpty() ? $cycleStartDate->toDateString() : $sortedReadings->last()->reading_date)
                ->get()
                ->toArray();
    
            $allPeriods = array_merge($existingPeriods, $billingPeriods);
    
            $uniquePeriods = [];
            $seen = [];
            foreach ($allPeriods as $period) {
                $startDate = Carbon::parse($period['start_date']);
                $endDate = Carbon::parse($period['end_date']);
                if ($period['status'] === 'Final Estimate' && 
                    $startDate->diffInDays($endDate) <= 1 && 
                    $endDate->toDateString() === $cycleStartDate->toDateString() && 
                    $period['end_reading_id'] === null) {
                    continue;
                }
                $key = $period['start_date'] . '|' . $period['end_date'];
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $uniquePeriods[] = $period;
                }
            }
            usort($uniquePeriods, function ($a, $b) {
                return strcmp($a['start_date'], $b['start_date']);
            });
    
            foreach ($uniquePeriods as $period) {
                BillingPeriod::updateOrCreate(
                    [
                        'meter_id' => $meterId,
                        'start_date' => $period['start_date'],
                        'end_date' => $period['end_date'],
                    ],
                    $period
                );
            }
        }
    
    
        return $billingPeriods;
    }












    public function addFixedCost(Request $request)
    {

        $postData = $request->post();
        $requiredFields = ['account_id', 'user_id'];
        $validated = validateData($requiredFields, $postData);
        if (!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        if (!empty($postData['fixed_cost'])) {
            foreach ($postData['fixed_cost'] as $fixedCost) {

                if (empty($fixedCost['title']) || empty($fixedCost['value']))
                    continue;

                $costArr['account_id'] = $postData['account_id'];
                $costArr['title'] = $fixedCost['title'];
                $costArr['value'] = $fixedCost['value'];
                $costArr['added_by'] = $postData['user_id'];
                if (isset($fixedCost['is_active']))
                    $costArr['is_active'] = $fixedCost['is_active'];

                if (empty($fixedCost['id']))
                    FixedCost::create($costArr);
                else
                    FixedCost::where('id', $fixedCost['id'])->update($costArr);
            }
        }

        // Check if user has deleted any fixed costs or not
        if (!empty($postData['removed_fixed_costs_ids'])) {
            $removedCostIDs = $postData['removed_fixed_costs_ids'];
            foreach ($removedCostIDs as $removedCostID)
                FixedCost::where('id', $removedCostID)->delete();
        }

        // Get all fixed costs for the account
        $res = Account::with('fixedCosts')->where('id', $postData['account_id'])->get();
        if ($res)
            return response()->json(['status' => true, 'code' => 200, 'data' => $res, 'msg' => 'Fixed cost added successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function addDefaultCost(Request $request)
    {

        $postData = $request->post();
        if (empty($postData['account_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);

        if (!empty($postData['default_fixed_cost'])) {
            foreach ($postData['default_fixed_cost'] as $defaultCost) {
                if (empty($defaultCost['id']))
                    continue;

                $upd = [];
                if (!empty($defaultCost['value']))
                    $upd['value'] = $defaultCost['value'];

                if (isset($defaultCost['is_active']))
                    $upd['is_active'] = $defaultCost['is_active'];

                if (!empty($upd))
                    AccountFixedCost::where('id', $defaultCost['id'])->update($upd);
            }
        }

        // Get all fixed costs for the account
        $res = AccountFixedCost::where('account_id', $postData['account_id'])->get();
        if ($res)
            return response()->json(['status' => true, 'code' => 200, 'data' => $res, 'msg' => 'Default cost added successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function updateMeter(Request $request)
    {

        $postData = $request->post();
        $requiredFields = ['meter_id', 'meter_reading_id', 'account_id', 'meter_type_id', 'meter_title', 'meter_number', 'meter_reading_date', 'meter_reading'];
        $validated = validateData($requiredFields, $postData);
        if (!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $meter = Meter::find($postData['meter_id']);
        $meterReading = MeterReadings::find($postData['meter_reading_id']);
        if (empty($meter) || empty($meterReading))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, wrong meter_id or meter_reading_id provided!']);

        $meter->account_id = $postData['account_id'];
        $meter->meter_type_id = $postData['meter_type_id'];
        $meter->meter_title = $postData['meter_title'];
        $meter->meter_number = $postData['meter_number'];

        if ($meter->save()) {

            //$meterReading->reading_date = date('Y-m-d', strtotime($postData['meter_reading_date']));
            $meterReading->reading_date = $postData['meter_reading_date'];
            $meterReading->reading_value = $postData['meter_reading'];
            $meterReading->save();

            $data = Meter::with('readings')->find($meterReading->id);

            return response()->json(['status' => true, 'code' => 200, 'data' => $data, 'msg' => 'Meter updated successfully!']);
        } else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getMeter(Request $request)
    {


        $accountID = $request->get('account_id');

        if (empty($accountID))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);
        $account = Account::find($accountID);
        if (empty($account))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Account not found!']);

        $property = $account->property_id ? Property::find($account->property_id) : null;


        $meters = Meter::with('readings')->where('account_id', $accountID)->get();
        Log::info($property);

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meters retrieved successfully!', 'data' => $meters, 'property' => $property]);
    }

    public function getMeterReadings(Request $request)
    {

        $meterID = $request->get('meter_id');
        if (empty($meterID))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, meter_id is required!']);

        $meters = Meter::with('readings')->where('id', $meterID)->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meters retrieved successfully!', 'data' => $meters]);
    }

    public function addMeterReadings(Request $request)
    {
        $postData = $request->post();
        $requiredFields = ['meter_id', 'meter_reading_date', 'meter_reading'];
        $validated = validateData($requiredFields, $postData);
        if (!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);
    
        $readingImg = null;
    
        if ($request->has('reading_image') && !empty($request->input('reading_image'))) {
            $base64Image = $request->input('reading_image');
            if (preg_match('#^data:image/\w+;base64,#i', $base64Image)) {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                $fileName = 'reading_' . time() . '.png';
                $storagePath = 'public/readings/' . $fileName;
                $publicPath = 'storage/readings/' . $fileName;
    
                Storage::put($storagePath, $imageData);
                $readingImg = $publicPath;
            }
        }
    
        // Convert the reading date to "Y-m-d H:i:s" format
        $formattedDate = Carbon::parse($postData['meter_reading_date'])->format('Y-m-d H:i:s');
    
        $siteArr = array(
            'meter_id' => $postData['meter_id'],
            'reading_date' => $formattedDate,  // Save formatted date
            'reading_value' => $postData['meter_reading'],
            'reading_image' => $readingImg
        );
    
        $res = MeterReadings::create($siteArr);
    
        if ($res)
            return response()->json(['status' => true, 'code' => 200, 'data' => $res, 'msg' => 'Meter readings added successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }


    public function deleteMeter(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['meter_id'])) {
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, meter_id is required!']);
        }

        // Get meter
        $meter = Meter::where('id', $postData['meter_id'])->first();

        if (!$meter) {
            return response()->json(['status' => false, 'code' => 404, 'msg' => 'Meter not found!']);
        }

        // Check if the meter was created within the last 1 hour
        $oneHourAgo = now()->subHour();
        if ($meter->created_at < $oneHourAgo) {
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Meter cannot be deleted after one hour of creation.']);
        }

        DB::beginTransaction();
        try {
            // Delete the meter
            $meter->delete();
            DB::commit();

            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meter removed successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'code' => 500, 'msg' => 'Oops, something went wrong!']);
        }
    }



    public function deleteMeterReading(Request $request)
    {

        $postData = $request->post();

        if (empty($postData['reading_id'])) {
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, reading_id is required!'], 400);
        }

        $reading = MeterReadings::find($postData['reading_id']);

        if (!$reading) {
            return response()->json(['status' => false, 'code' => 404, 'msg' => 'Reading not found!'], 404);
        }

        $oneHourAgo = now()->subHour();
        if ($reading->created_at < $oneHourAgo) {
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'You can only delete readings created within the last hour.'], 400);
        }

        if ($reading->delete()) {
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meter reading removed successfully!'], 200);
        }

        return response()->json(['status' => false, 'code' => 500, 'msg' => 'Oops, something went wrong!'], 500);
    }


    //get latest usages of meter
    public function getLatestUsage(Request $request)
    {
        $meterId = $request->query('meter_id');

        // Fetch the latest reading
        $latestReading = MeterReadings::where('meter_id', $meterId)
            ->latest('created_at')
            ->first();

        if (!$latestReading) {
            return response()->json([
                'status' => false,
                'msg' => 'No readings found for this meter.',
            ], 404);
        }

        // Fetch the previous reading
        $previousReading = MeterReadings::where('meter_id', $meterId)
            ->where('created_at', '<', $latestReading->created_at)
            ->latest('created_at')
            ->first();

        if (!$previousReading) {
            return response()->json([
                'status' => false,
                'msg' => 'Not enough readings to calculate usage.',
            ], 400);
        }

        // Calculate usage
        $usage = $latestReading->reading_value - $previousReading->reading_value;

        // Calculate cost (assuming $0.10 per unit)
        $ratePerUnit = 0.10; // You can fetch this from a settings table or configuration
        $cost = $usage * $ratePerUnit;

        return response()->json([
            'status' => true,
            'latestReading' => $latestReading,
            'previousReading' => $previousReading,
            'usage' => $usage,
            'cost' => $cost,
        ]);
    }





    public function getMeterTypes(Request $request)
    {

        $meters = MeterType::all();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meters retrieved successfully!', 'data' => $meters]);
    }

    public function verifyEmail(Request $request)
    {

        $postData = $request->post();
        if (empty($postData['email']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, email field is required!']);

        // Now check if this email exists in our system or not
        $result = User::where('email', $postData['email'])->get();
        if (count($result) == 0)
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'No user exists under this email in our system!']);

        // Now that email exists in our system, make a reset code
        $digits = 5;
        $code = rand(pow(10, $digits - 1), pow(10, $digits) - 1);

        // Add this code into the database column
        $upd = User::where('email', $postData['email'])->update(['password_reset_code' => $code]);
        if (!$upd)
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);

        // Send the code to user email
        require base_path("vendor/autoload.php");

        $mail = new PHPMailer(env('MAILER_DEBUG', false));     // Passing `true` enables exceptions

        $mailerHost = env('MAIL_HOST');
        $mailerUsername = env('MAIL_USERNAME');
        $mailerPassword = env('MAIL_PASSWORD');
        $mailerFrom = env('MAIL_FROM_ADDRESS');
        if (empty($mailerHost) || empty($mailerUsername) || empty($mailerPassword) || empty($mailerFrom))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, mailer setting is missing.']);

        try {
            // Email server settings
            $mail->SMTPDebug = 2;  // Enable SMTP debug output (won't show in JSON response but will log details)
            $mail->Debugoutput = function ($str, $level) {
                error_log("SMTP Debug Level $level: $str");
            };

            $mail->isSMTP();
            $mail->Host = $mailerHost;
            $mail->SMTPAuth = true;
            $mail->Username = $mailerUsername;
            $mail->Password = $mailerPassword;
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $mail->Port = 587;

            $mail->setFrom($mailerUsername, 'LightsAndWater');
            $mail->addAddress($postData['email']);
            $mail->addReplyTo($mailerUsername, 'LightsAndWater');

            $mail->isHTML(true);
            $mail->Subject = "Password Reset Code";
            $mail->Body    = "Hi, we have received your password reset request. Please use the following code: " . $code;

            if (!$mail->send()) {
                error_log("PHPMailer Error: " . $mail->ErrorInfo);
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Error while sending email: ' . $mail->ErrorInfo]);
            }
        } catch (\Exception $e) {
            error_log("Exception: " . $e->getMessage());
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Exception: ' . $e->getMessage()]);
        }


        return response()->json(['status' => true, 'code' => 200, 'msg' => 'A code has been sent to your email.!']);
    }

    public function verifyCode(Request $request)
    {

        $postData = $request->post();
        if (empty($postData['code']) || empty($postData['email']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, code and email fields are required!']);

        $user = User::where(['email' => $postData['email'], 'password_reset_code' => $postData['code']])->get();
        if (count($user) == 1) {
            User::where(['email' => $postData['email'], 'password_reset_code' => $postData['code']])->update(['password_reset_code' => null]);
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Code matched. Please proceed!']);
        } else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Code did not matched!']);
    }

    public function resetPassword(Request $request)
    {

        $postData = $request->post();
        if (empty($postData['password']) || empty($postData['email']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, password and email fields are required!']);

        $user = User::where(['email' => $postData['email']])->get();
        if (count($user) == 1) {
            User::where(['email' => $postData['email']])->update(['password' => bcrypt($postData['password'])]);
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Code matched. Please proceed!']);
        } else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Wrong email provided!']);
    }



    public function updateMeterReadings(Request $request)
{
    $postData = $request->post();
    $requiredFields = ['meter_reading_id', 'meter_id', 'meter_reading_date', 'meter_reading'];
    $validated = validateData($requiredFields, $postData);

    if (!$validated['status']) {
        return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);
    }

    $reading = MeterReadings::find($postData['meter_reading_id']);
    if (empty($reading)) {
        return response()->json(['status' => false, 'code' => 404, 'msg' => 'Oops, wrong meter_reading_id provided!']);
    }

    // Check if the meter reading was created within the last 1 hour
    $oneHourAgo = now()->subHour();
    if ($reading->created_at < $oneHourAgo) {
        return response()->json(['status' => false, 'code' => 400, 'msg' => 'Meter reading cannot be updated after one hour of creation.']);
    }

    // Convert the reading date to "Y-m-d H:i:s" format
    $formattedDate = Carbon::parse($postData['meter_reading_date'])->format('Y-m-d H:i:s');

    // Update the meter reading
    $reading->meter_id = $postData['meter_id'];
    $reading->reading_date = $formattedDate;  // Save formatted date
    $reading->reading_value = $postData['meter_reading'];

    if ($reading->save()) {
        return response()->json(['status' => true, 'code' => 200, 'data' => $reading, 'msg' => 'Meter reading updated successfully!']);
    } else {
        return response()->json(['status' => false, 'code' => 500, 'msg' => 'Oops, something went wrong!']);
    }
}


    public function getTC()
    {

        $settings = Settings::first();
        $data = ['id' => $settings->id, 'terms_condition' => $settings->terms_condition];
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Terms and conditions retrieved successfully!', 'data' => $data]);
    }

    public function getAds()
    {

        $ads = Ads::with('category')->get();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Ads retrieved  successfully!', 'data' => $ads]);
    }

    public function getFixedCosts(Request $request)
    {

        $postData = $request->post();
        if (empty($postData['account_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);

        $fixedCosts = FixedCost::with('account')->where('account_id', $postData['account_id'])
            ->where('is_default', 0)->get();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Fixed costs retrieved  successfully!', 'data' => $fixedCosts]);
    }

    public function getDefaultCosts()
    {

        $defaultCosts = FixedCost::select('id', 'account_id', 'title', 'value', 'is_default', 'created_at')->where('is_default', 1)->get();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Default costs retrieved  successfully!', 'data' => $defaultCosts]);
    }

    public function getAdsCategories(Request $request)
    {

        $postData = $request->post();
        if (!empty($postData['category_id']))
            $categories = AdsCategory::with(['ads', 'childs'])->where('id', $postData['category_id'])->WhereNull('parent_id')->get();
        else
            $categories = AdsCategory::with(['ads', 'childs'])->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Ads categories retrieved successfully!', 'data' => $categories]);
    }

    public function getAlarms(Request $request)
    {

        $postData = $request->post();
        $whereArr = [];
        if (!empty($postData['date']))
            $whereArr['date'] = date("Y-m-d", strtotime($postData['date']));

        if (!empty($postData['region_id']))
            $whereArr['region_id'] = $postData['region_id'];

        if (!empty($whereArr))
            $alarms = RegionAlarms::where($whereArr)->get();
        else
            $alarms = RegionAlarms::all();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Alarms retrieved successfully!', 'data' => $alarms]);
    }

    public function getRegions()
    {

        $regions = Regions::select('id', 'name')->get();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Regions retrieved  successfully!', 'data' => $regions]);
    }
    public function getAccountTypes()
    {

        $accountType = AccountType::select('id', 'type')->get();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account Type retrieved  successfully!', 'data' => $accountType]);
    }
    public function getRegionEmails($id)
    {
        if (!empty($id)) {
            $regions = Regions::select('water_email', 'electricity_email')->where('id', $id)->first();
            return $regions;
        }
        return false;
    }
    public function getDefaultCostAccount($uid)
    {
        $fixedCosts = Account::with(['defaultFixedCosts.fixedCost'])->find($uid);
        //echo "<pre>";print_r($fixedCosts);exit()
        $fixedCostArr = [];
        $user_additional_cost = [];
        $rates_rebate = 0;
        $rates = 0;
        if (isset($fixedCosts['defaultFixedCosts']) && !empty($fixedCosts['defaultFixedCosts'])) {
            foreach ($fixedCosts['defaultFixedCosts'] as $key => $value) {
                if ($value->is_active == 1) {
                    if (isset($value['fixedCost']) && !empty($value['fixedCost'])) {
                        if ($value['fixedCost']['title'] == "Water Loss Levy" || $value['fixedCost']['title'] == "Refuse Collection") {
                            $user_additional_cost[] = array(
                                'title' => $value['fixedCost']['title'],
                                'total' => $value['value']
                            );
                        } elseif ($value['fixedCost']['title'] == "Rates Rebate") {
                            $rates_rebate  = $value['value'];
                        } elseif ($value['fixedCost']['title'] == "Rates") {
                            $rates  = $value['value'];
                        } else {
                            $fixedCostArr[] = array(
                                'title' => $value['fixedCost']['title'],
                                'total' => $value['value']
                            );
                        }
                    }
                }
            }
        }
        return ['fixedCost' => $fixedCostArr, 'user_additional_cost' => $user_additional_cost, 'rate_rebate' => $rates_rebate, 'rates' => $rates];
    }
    public function getEastimateCost(Request $request)
    {

        $postData = $request->post();

        $water_fullbill = [];
        $electricity_fullbill = [];

        if (!isset($postData['account_id']) && empty($postData['account_id'])) {
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);
        }

        // if (!isset($postData['meter_id']) && empty($postData['meter_id'])) {
        //     return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, meter_id is required!']);
        // }
        $account = Account::where('id', $postData['account_id'])->first();
        $region_cost_full_bill = RegionsAccountTypeCost::where('region_id', $account->region_id)->where('account_type_id', $account->account_type_id)->first();

        if (empty($region_cost_full_bill)) {

            $response = [
                'status' => false,
                'code' => 400,
                'msg' => 'In Account Please select region and account type',
                'data' => []
            ];
            return $response;
            //return response()->json(['status' => false, 'code' => 400, 'msg' => 'In Account Please select region and account type', 'data' => []]);
        }
        if (isset($postData['meter_id']) && !empty($postData['meter_id'])) {
            $meters = Meter::with('readings')->where('id', $postData['meter_id'])->get();
        } else {
            $meters = Meter::with('readings')->where('account_id', $postData['account_id'])->get();
        }

        // echo "<pre>";
        // print_r(count($meters));
        // exit();
        if (count($meters) > 0) {
            foreach ($meters as $meter) {
                $response[] = $this->getReadings($postData['account_id'], $meter, $postData['type'], $postData['start_date'], $postData['end_date']);
            }
            if ($postData['type'] == "fullbill") {
                if (isset($response) && !empty($response)) {
                    $account = Account::where('id', $postData['account_id'])->first();

                    $region_cost_full_bill = RegionsAccountTypeCost::where('region_id', $account->region_id)->where('account_type_id', $account->account_type_id)->first();
                    $ele_total  = 0;
                    $water_total  = 0;
                    $additional_total  = 0;
                    $subtotal_all_cost = 0;
                    $grand_total = 0;
                    $default_fixed_cost = $this->getDefaultCostAccount($postData['account_id']);
                    $user_rate_rebate = 0;
                    $user_rates = 0;

                    $user_rate_rebate = isset($default_fixed_cost['rate_rebate']) ? $default_fixed_cost['rate_rebate'] : 0;
                    $user_rates = isset($default_fixed_cost['rates']) ? $default_fixed_cost['rates'] : 0;
                    // echo $user_rate_rebate;
                    // echo $user_rates;

                    $user_additional_cost = isset($default_fixed_cost['user_additional_cost']) ? $default_fixed_cost['user_additional_cost'] : [];
                    //echo "<pre>";print_r($response);exit();
                    foreach ($response as $key => $value) {
                        $water_in_project = [];
                        $water_out_project = [];
                        $vat = [];
                        // echo "<pre>";printf($value->type);
                        if (isset($value->type) && $value->type == 1) {
                            $water_fullbill[$value->meter_number]['type'] = $value->type;
                            $water_fullbill[$value->meter_number]['usage'] = $value->usage;
                            $water_fullbill[$value->meter_number]['meter_number'] = $value->meter_number;
                            $water_fullbill[$value->meter_number]['meter_title'] = $value->meter_title;
                            $water_fullbill[$value->meter_number]['water_email'] = $value->water_email;

                            $water_in_project[] = array(
                                'title' => 'water In',
                                'use' => $value->usage,
                                'total' => isset($value->water_in_total) ? $value->water_in_total : 0
                            );
                            $water_out_project[] = array(
                                'title' => 'water Out',
                                'total' => isset($value->water_out_total) ? $value->water_out_total : 0
                            );
                            $waterin_additional = isset($value->waterin_additional) ? $value->waterin_additional : [];
                            $waterout_additional = isset($value->waterout_additional) ? $value->waterout_additional : [];

                            $water_fullbill[$value->meter_number]['projection'] = array_merge($water_in_project, $water_out_project, $waterin_additional, $waterout_additional, $vat);
                            $water_total += array_sum(array_column($water_fullbill[$value->meter_number]['projection'], 'total'));
                            //$water_fullbill['water_total'] = $water_total;
                        } elseif (isset($value->type) && $value->type == 2) {
                            // electricity
                            $electricity_fullbill[$value->meter_number]['type'] = $value->type;
                            $electricity_fullbill[$value->meter_number]['usage'] = $value->usage;
                            $electricity_fullbill[$value->meter_number]['meter_number'] = $value->meter_number;
                            $electricity_fullbill[$value->meter_number]['meter_title'] = $value->meter_title;
                            $electricity_fullbill[$value->meter_number]['electricity_email'] = $value->electricity_email;
                            $ele[] = array(
                                'title' => 'electricity',
                                'use' => $value->usage,
                                'total' => $value->electricity_total,
                            );
                            $electricity_additional = isset($value->electricity_additional) ? $value->electricity_additional : [];

                            $electricity_fullbill[$value->meter_number]['projection'] = array_merge($ele, $electricity_additional);
                            $ele_total += array_sum(array_column($electricity_fullbill[$value->meter_number]['projection'], 'total'));
                        }
                    }
                    $user_additional_cost = FixedCost::select('title', 'value as total')->where('is_active', 1)->where('account_id', $postData['account_id'])->get()->toArray();
                    if (isset($user_additional_cost) && !empty($user_additional_cost)) {
                        $use_plus_admin_additional_cost = $user_additional_cost;
                    } else {
                        $use_plus_admin_additional_cost = array_merge($user_additional_cost, $value->common_additional);
                    }
                    // $use_plus_admin_additional_cost = $user_additional_cost;//array_merge($user_additional_cost, $value->common_additional);
                    //echo "<pre>";print_r($use_plus_admin_additional_cost);exit();
                    $additional_total += array_sum(array_column($use_plus_admin_additional_cost, 'total'));
                    //  echo $additional_total;exit();

                    $response_fullbill['water'] = $water_fullbill;
                    $response_fullbill['electricity'] = $electricity_fullbill;
                    $response_fullbill['additional'] = isset($use_plus_admin_additional_cost) ? $use_plus_admin_additional_cost : [];
                    $subtotal_all_cost = number_format($water_total + $ele_total + $additional_total, 2, '.', '');
                    $vat = $subtotal_all_cost * $region_cost_full_bill->vat_percentage / 100;
                    $sub_total_vat  = number_format($vat, 2, '.', '');
                    $grand_total = ($subtotal_all_cost + $sub_total_vat + $user_rates) - $user_rate_rebate;
                    $response_fullbill['final_total'] = array(
                        'subtotal_of_all_cost' => $subtotal_all_cost,
                        'vat' => $sub_total_vat,
                        'total_including_vat' => number_format($subtotal_all_cost + $sub_total_vat, 2, '.', ''),
                        'rates' => number_format($user_rates, 2, '.', ''), // get from customer input,
                        'rebate' => number_format($user_rate_rebate, 2, '.', ''), // get from customer input
                        'grand_total' => number_format($grand_total, 2, '.', '')
                    );
                    if (isset($response_fullbill) && !empty($response_fullbill)) {
                        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Full bill get successfully!', 'data' => $response_fullbill]);
                    } else {
                        return response()->json(['status' => false, 'code' => 400, 'msg' => 'Cost Not Found in Admin Side', 'data' => []]);
                    }
                }
            }
            if ((isset($response) && !empty($response)) && !empty($postData['meter_id'])) {
                return response()->json($response);
            } else {
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Cost Not Found in Admin Side', 'data' => []]);
            }

            if (isset($response) && !empty($response)) {
                return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meters retrieved successfully!', 'data' => $response]);
            } else {
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Cost Not Found in Admin Side', 'data' => []]);
            }
        } else {
            $response = [
                'status' => false,
                'code' => 400,
                'msg' => 'Please add meter in this account',
                'data' => []
            ];
            return $response;
        }
    }
    function find_closest($array, $date)
    {
        foreach ($array as $day) {
            $interval[] = abs(strtotime($date) - strtotime($day['date']));
        }
        asort($interval);
        $closest = key($interval);
        return $array[$closest];
    }
    public function getReadings($accountID, $meter, $bill_type, $start_date, $end_date)
    {

        if ($meter) {
            // water
            // $metersReading = MeterReadings::where('meter_id', 255)->get();
            $account = Account::where('id', $accountID)->first();
            $metersReading = MeterReadings::where('meter_id', $meter->id)->get();

            if (isset($metersReading) && !empty($metersReading)) {
                foreach ($metersReading as $key => $value) {
                    $reading_dates[] = array(
                        'date' => $value->reading_date,
                        'reading_value' => $value->reading_value
                    );
                }
                usort($reading_dates, function ($a, $b) {
                    return $a['date'] <=> $a['date'];
                });


                // $closestReading = null;
                // foreach ($reading_dates as $reading) {

                //     $getOnlyDate = date('d', strtotime($reading['date']));
                //     if ($getOnlyDate == 15) {
                //         $closestReading = $reading;
                //     } elseif ($getOnlyDate <= 15) {
                //         $closestReading = $reading;
                //     }
                // }
                // $closefirstReadingDate = "";
                // $closefirstReadingDate = date('Y-m-d', strtotime($closestReading['date'])) ?? null;
                // $closefirstReading = $closestReading['reading_value'] ?? null;


                if (count($reading_dates) >= 2) {
                    $reading_arr = array_slice($reading_dates, -2, 2, true);
                    usort($reading_arr, function ($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });
                    $previous_date = $this->find_closest($reading_dates, $start_date);
                    $next_date = $this->find_closest($reading_dates, $end_date);
                    $firstReadingDate = date('Y-m-d', strtotime($previous_date['date'])) ?? null;
                    $firstReading = $previous_date['reading_value'] ?? null;
                    $endReadingDate = date('Y-m-d', strtotime($next_date['date'])) ?? null;
                    $endReading = $next_date['reading_value'] ?? null;
                    // if (!empty($closefirstReadingDate)) {
                    //     $firstReadingDate = date('Y-m-d', strtotime($closestReading['date'])) ?? null;
                    //     $firstReading = $closestReading['reading_value'] ?? null;
                    // } else {
                    //     $firstReadingDate = date('Y-m-d', strtotime($reading_arr[0]['date'])) ?? null;
                    //     $firstReading = $reading_arr[0]['reading_value'] ?? null;
                    // }
                    // $endReadingDate = date('Y-m-d', strtotime($reading_arr[1]['date'])) ?? null;
                    // $endReading = $reading_arr[1]['reading_value'] ?? null;

                    if (!empty($firstReadingDate) && !empty($firstReading) && !empty($endReadingDate) && !empty($endReading)) {
                        $time1 = strtotime($firstReadingDate);
                        $time2 = strtotime($endReadingDate);

                        $daydiff = floor(($time2 - $time1) / 86400);
                        $final_reading = $endReading - $firstReading;
                        if ($meter->meter_type_id == 1) {
                            // water
                            $final_reading = substr($final_reading, 0, -4);
                        } else {
                            // electricity
                            $final_reading = substr($final_reading, 0, -1);
                        }

                        // previous reading and last reading same then error
                        if (isset($final_reading) && !empty($final_reading)) {
                            $response = $this->getWaterBill($accountID, $final_reading, $meter, $daydiff, $bill_type);
                            // echo "<pre>";print_r($response);exit();
                            if (isset($response) && !empty($response)) {
                                $account = Account::where('id', $accountID)->first();
                                if (isset($account->bill_day) && !empty($account->bill_day)) {
                                    $bill_day = $account->bill_day;
                                    $current_month = date($bill_day . ' F, Y');
                                    $current_month = date("d F Y", strtotime($current_month));
                                    $previous_month = date('d F Y', strtotime('-1 months', strtotime($current_month)));
                                    $response->firstReadingDate = $previous_month;
                                    $response->endReadingDate = $current_month;
                                }
                                //$response->firstReadingDate = date('d F Y', strtotime($firstReadingDate)) ?? null;
                                //$response->endReadingDate = date('d F Y', strtotime($endReadingDate)) ?? null;
                                if ($meter->meter_type_id == 1) {
                                    // water
                                    $response->firstReading = substr($firstReading, 0, -4) ?? 0;
                                    $response->endReading = substr($endReading, 0, -4) ?? 0;
                                } else {
                                    // electricity
                                    $response->firstReading = substr($firstReading, 0, -1) ?? 0;
                                    $response->endReading = substr($endReading, 0, -1) ?? 0;
                                }
                                return $response;
                            }
                        } else {
                            return [
                                'status' => false,
                                'code' => 400,
                                'msg' => 'Your current reading should be greater than the previous reading.',
                                'data' => []
                            ];
                        }
                    } else {
                        return [
                            'status' => false,
                            'code' => 400,
                            'msg' => 'You have invalid meter reading.',
                            'data' => []
                        ];
                    }
                } else {
                    return [
                        'status' => false,
                        'code' => 400,
                        'msg' => 'Please enter Previous and Current reading',
                        'data' => []
                    ];
                }
            }
        }
    }
    public function getWaterBill($accountID, $reading, $meters, $daydiff, $bill_type)
    {
        //echo "<pre>";print_r($daydiff);exit;
        // if ($reading > 0 && $daydiff > 0) {
        //     $reading = number_format($reading / $daydiff * 31, 2, '.', '');
        // } else {
        //     $reading = $reading;
        // }
        $reading = $reading;
        $type_id = $meters->meter_type_id; // meter type = 1 - water, 2 - electricity
        $meter_id = $meters->id;
        $meter_number = $meters->meter_number;
        $meter_title = $meters->meter_title;
        $account = Account::where('id', $accountID)->first();

        $region_cost = RegionsAccountTypeCost::where('region_id', $account->region_id)->where('account_type_id', $account->account_type_id)->first();

        if (isset($region_cost) && !empty($region_cost)) {
            $water_in_total  = 0;
            $sub_total  = 0;
            $water_out_total = 0;
            $electricity_total  = 0;
            $ratable_value = 0;
            $month_day = 31;
            $rebate = 0;
            $waterin_additional_total = 0;
            $waterout_additional_total = 0;
            $electricity_additional_total = 0;
            $region_cost->bill_day = $account->bill_day;

            if ($region_cost->ratable_value == 0) {
                $ratable_value = 250000;
            } else {
                $ratable_value = $region_cost->ratable_value;
            }
            // actual calculation
            $common_additional = json_decode($region_cost->additional);

            if (isset($common_additional) && !empty($common_additional)) {
                foreach ($common_additional as $key => $value) {
                    $data[] = array('title' => $value->name, 'total' => $value->cost);
                }
                $region_cost->common_additional = $data;
            } else {
                $region_cost->common_additional = [];
            }
            if ($type_id == 1) {
                // water
                $water_remaning = $reading;
                $electricity_remaning = 0;
                $unit = "kl";

                if ($water_remaning > 0) {
                    // echo $water_remaning;exit();
                    // water in logic
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
                        }
                    }
                    // addtional water in logic
                    $waterin_additional = [];
                    if ($region_cost->waterin_additional) {
                        $waterin_additional = json_decode($region_cost->waterin_additional);
                        if (isset($waterin_additional) && !empty($waterin_additional)) {
                            foreach ($waterin_additional as $key => $value) {
                                if ($value->percentage == null) {
                                    $cal_total = $value->cost;
                                } else {
                                    $cal_total = $reading * $value->percentage / 100 * $value->cost;
                                }
                                $waterin_additional[$key]->total =  number_format($cal_total, 2, '.', '');
                                $waterin_additional_total += number_format($cal_total, 2, '.', '');
                            }
                            $region_cost->water_in_related_total = number_format($waterin_additional_total, 2, '.', '');
                            $region_cost->waterin_additional = $waterin_additional;
                            $waterin_additional = $waterin_additional;
                        }
                    }
                    // echo "<pre>";print_r($waterin_additional_total);exit();

                    //water out logic
                    if (!empty($region_cost->water_out)) {
                        $water_out = json_decode($region_cost->water_out);
                        $water_out_remaning = $reading; //$region_cost->water_used;
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
                            //  $region_cost->water_out = json_encode($water_out);
                        }
                    }

                    // addtional water out logic
                    $waterout_additional = [];
                    if ($region_cost->waterout_additional) {
                        $waterout_additional = json_decode($region_cost->waterout_additional);
                        if (isset($waterout_additional) && !empty($waterout_additional)) {
                            foreach ($waterout_additional as $key => $value) {
                                if ($value->percentage == null) {
                                    $cal_total = $value->cost;
                                } else {
                                    $cal_total = $reading * $value->percentage / 100 * $value->cost;
                                }
                                $waterout_additional[$key]->total = number_format($cal_total, 2, '.', '');
                                $waterout_additional_total += number_format($cal_total, 2, '.', '');
                            }
                            $region_cost->water_out_related_total = number_format($waterout_additional_total, 2, '.', '');
                            $region_cost->waterout_additional = $waterout_additional;
                            $waterout_additional = $waterout_additional;
                        }
                    }
                }
                // additional cost
                $additional = json_decode($region_cost->additional);

                $sub_total = $water_in_total + $water_out_total + $electricity_total + $waterin_additional_total + $waterout_additional_total + $electricity_additional_total;

                if ($bill_type == "fullbill") {

                    if (isset($additional) && !empty($additional)) {
                        foreach ($additional as $key => $value) {
                            if ($value->cost >= 0) {
                                $sub_total += $value->cost;
                            } else {
                                $rebate += $value->cost;
                            }
                        }
                    }
                }
                // start usages logic
                $region_cost->usage = $reading;
                $region_cost->usage_days = $daydiff;

                $region_cost->daily_usage = number_format($reading / $daydiff, 2, '.', '') . ' ' . $unit;
                $daily_uages = $reading / $daydiff;
                // $region_cost->monthly_usage =  number_format($reading / $daydiff * $month_day, 2, '.', ''). ' ' . $unit;
                $monthly_uages =  $daily_uages * $month_day;
                $region_cost->monthly_usage =  number_format($monthly_uages, 2, '.', '') . ' ' . $unit;

                // end usages logic
                $subtotal_final = $sub_total - abs($rebate);

                $region_cost->sub_total = number_format($subtotal_final, 2, '.', '');

                $sub_total_vat = $subtotal_final * $region_cost->vat_percentage / 100;
                $region_cost->sub_total_vat = number_format($sub_total_vat, 2, '.', '');

                $final_total  = $subtotal_final + $sub_total_vat + $region_cost->vat_rate;
                $region_cost->final_total = number_format($final_total, 2, '.', '');


                $water_in_project[] = array(
                    'title' => 'water In',
                    'total' => number_format($water_in_total, 2, '.', '')
                );
                $water_out_project[] = array(
                    'title' => 'water Out',
                    'total' => number_format($water_out_total, 2, '.', '')
                );

                $vat[] = array(
                    'title' => 'VAT',
                    'total' => number_format($region_cost->sub_total_vat, 2, '.', '')
                );

                $region_cost->projection = array_merge($water_in_project, $water_out_project, $waterin_additional, $waterout_additional, $vat);
                $region_cost->daily_cost = number_format($region_cost->final_total / $month_day, 2, '.', '');
                $region_cost->meter_number = $meter_number;
                $region_cost->meter_title = $meter_title;
                $region_cost->type = $type_id;
                $region_cost->meter_id = $meter_id;
                $region_cost->water_email = $region_cost->water_email;
                unset(
                    $region_cost->water_in,
                    $region_cost->water_out,
                    $region_cost->template_name,
                    $region_cost->read_day,
                    $region_cost->start_date,
                    $region_cost->end_date,
                    $region_cost->water_used,
                    $region_cost->electricity_used,
                    // $region_cost->electricity,
                    //  $region_cost->electricity_additional,
                    // $region_cost->additional,
                    $region_cost->billing_date,
                    $region_cost->reading_date,
                    $region_cost->created_at,
                    $region_cost->updated_at,
                    //$region_cost->waterin_additional,
                    //$region_cost->waterout_additional,

                );
                return $region_cost;
            } elseif ($type_id == 2) {
                $electricity_remaning = $reading;
                $water_remaning = 0;
                $unit = "kWh";
                $region_cost->electricity_used = $electricity_remaning;
                // electricity

                if ($electricity_remaning > 0) {

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
                        // $region_cost->electricity = json_encode($electricity);
                    }
                    // additional cost for electricity
                    $electricity_additional = [];
                    if ($region_cost->electricity_additional) {
                        $electricity_additional = json_decode($region_cost->electricity_additional);
                        if (isset($electricity_additional) && !empty($electricity_additional)) {
                            foreach ($electricity_additional as $key => $value) {
                                if ($value->percentage == null) {
                                    $cal_total = $value->cost;
                                } else {
                                    $cal_total = $region_cost->electricity_used * $value->percentage / 100 * $value->cost;
                                }
                                $electricity_additional[$key]->total =  number_format($cal_total, 2, '.', '');
                                $electricity_additional_total += number_format($cal_total, 2, '.', '');
                            }
                            $region_cost->electricity_related_total = number_format($electricity_additional_total, 2, '.', '');
                            $region_cost->electricity_additional = $electricity_additional;
                            $electricity_additional = $region_cost->electricity_additional;
                        }
                    }
                }

                $additional = json_decode($region_cost->additional);
                $sub_total = $electricity_total  + $electricity_additional_total;
                if (isset($additional) && !empty($additional)) {
                    foreach ($additional as $key => $value) {
                        if ($value->cost >= 0) {
                            $sub_total += $value->cost;
                        } else {
                            $rebate += $value->cost;
                        }
                    }
                }
                // start usages logic
                $region_cost->usage = $reading;
                $region_cost->usage_days = $daydiff;

                $region_cost->daily_usage = number_format($reading / $daydiff, 2, '.', '') . ' ' . $unit;
                $region_cost->monthly_usage =  number_format($reading / $daydiff * $month_day, 2, '.', '');

                // end usages logic
                $subtotal_final = $sub_total - abs($rebate);

                $region_cost->sub_total = number_format($subtotal_final, 2, '.', '');

                $sub_total_vat = $subtotal_final * $region_cost->vat_percentage / 100;
                $region_cost->sub_total_vat = number_format($sub_total_vat, 2, '.', '');

                $final_total  = $subtotal_final + $sub_total_vat + $region_cost->vat_rate;
                $region_cost->final_total = number_format($final_total, 2, '.', '');


                $electricity_project[] = array(
                    'title' => 'electricity',
                    'total' => number_format($electricity_total, 2, '.', '')
                );

                $vat[] = array(
                    'title' => 'VAT',
                    'total' => $region_cost->sub_total_vat
                );

                $region_cost->projection = array_merge($electricity_project, $electricity_additional, $vat);
                $region_cost->daily_cost = number_format($region_cost->final_total / $month_day, 2, '.', '');
                $region_cost->meter_number = $meter_number;
                $region_cost->meter_title = $meter_title;
                $region_cost->type = $type_id;
                $region_cost->meter_id = $meter_id;
                $region_cost->electricity_email = $region_cost->electricity_email;
                unset(
                    $region_cost->water_in,
                    $region_cost->water_out,
                    $region_cost->template_name,
                    $region_cost->read_day,
                    $region_cost->start_date,
                    $region_cost->end_date,
                    $region_cost->water_used,
                    $region_cost->electricity_used,
                    $region_cost->electricity,
                    // $region_cost->electricity_additional,
                    $region_cost->additional,
                    $region_cost->billing_date,
                    $region_cost->reading_date,
                    $region_cost->created_at,
                    $region_cost->updated_at,
                    $region_cost->waterin_additional,
                    $region_cost->waterout_additional
                );
                return $region_cost;
            }
        } else {
            return 0;
        }
    }
    public function getAdditionalCost(Request $request)
    {

        $postData = $request->post();

        // if (!isset($postData['account_id']) && empty($postData['account_id'])) {
        //     return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);
        // }
        // if (!isset($postData['region_id']) && empty($postData['region_id'])) {
        //     return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, region_id is required!']);
        // }
        // if (!isset($postData['account_type_id']) && empty($postData['account_type_id'])) {
        //     return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_type_id is required!']);
        // }
        $fixedCosts = FixedCost::select('title as name', 'value as cost', 'is_active')->where('account_id', $postData['account_id'])->get()->toArray();
        if (isset($fixedCosts) && !empty($fixedCosts)) {
            // after first time save

            $array_merged = $fixedCosts;

            if (isset($array_merged) && !empty($array_merged)) {
                foreach ($array_merged as $key => $value) {
                    if (isset($value['is_active']) && $value['is_active'] == 1) {
                        $array_merged[$key]['isApplicable'] = true;
                    } else {
                        $array_merged[$key]['isApplicable'] = false;
                    }
                }
            }
        }
        return response()->json($array_merged);
        $region_cost = RegionsAccountTypeCost::select('additional')->where('region_id', $postData['region_id'])->where('account_type_id', $postData['account_type_id'])->first();
        if (isset($region_cost['additional']) && !empty($region_cost['additional'])) {
            $additional_arr = json_decode($region_cost['additional'], true);
            if (isset($fixedCosts) && !empty($fixedCosts)) {
                // after first time save
                $result = array_udiff(
                    $additional_arr,
                    $fixedCosts,
                    fn($a, $b) => ($a['name'] ?? $a['name']) <=> ($b['name'] ?? $b['name'])
                );
                $array_merged = array_merge($fixedCosts, $result);
                if (isset($array_merged) && !empty($array_merged)) {
                    foreach ($array_merged as $key => $value) {
                        if (isset($value['is_active']) && $value['is_active'] == 1) {
                            $array_merged[$key]['isApplicable'] = true;
                        } else {
                            $array_merged[$key]['isApplicable'] = false;
                        }
                    }
                }
                return response()->json($array_merged);
            } else {
                if (isset($additional_arr) && !empty($additional_arr)) {
                    foreach ($additional_arr as $key => $value) {
                        $additional_arr[$key]['isApplicable'] = true;
                    }
                }
                return response()->json($additional_arr);
            }
        } else {
            return response()->json([]);
        }
        // echo "<pre>";print_r($region_cost);exit();

    }
    public function getBillday(Request $request)
    {

        $postData = $request->post();
        $acc = Account::where('id', $postData['account_id'])->first();
        if ($acc->bill_read_day_active == 0) {
            $acc->bill_read_day_active = false;
        } else {
            $acc->bill_read_day_active = true;
        }

        return response()->json($acc);
    }
}
