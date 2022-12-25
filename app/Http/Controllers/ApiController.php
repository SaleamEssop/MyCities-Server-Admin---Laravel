<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountFixedCost;
use App\Models\Ads;
use App\Models\AdsCategory;
use App\Models\FixedCost;
use App\Models\Meter;
use App\Models\MeterReadings;
use App\Models\MeterType;
use App\Models\RegionAlarms;
use App\Models\Regions;
use App\Models\Settings;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ApiController extends Controller
{
    public function addSite(Request $request) {

        $postData = $request->post();
        $requiredFields = ['user_id', 'title', 'lat', 'lng', 'address'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $siteArr = array(
            'user_id' => $postData['user_id'],
            'title' => $postData['title'],
            'lat' => $postData['lat'],
            'lng' => $postData['lng'],
            'address' => $postData['address']
        );
        if(!empty($postData['email']))
            $siteArr['email'] = $postData['email'];

        $res = Site::create($siteArr);
        if($res)
            return response()->json(['status' => true, 'code' => 200, 'data' => $res, 'msg' => 'Location added successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function updateSite(Request $request) {

        $postData = $request->post();
        $requiredFields = ['site_id', 'user_id', 'title', 'lat', 'lng', 'address'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $site = Site::find($postData['site_id']);
        if(empty($site))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, wrong site_id provided!']);

        $site->user_id = $postData['user_id'];
        $site->title = $postData['title'];
        $site->lat = $postData['lat'];
        $site->lng = $postData['lng'];
        $site->address = $postData['address'];

        if(!empty($postData['email']))
            $site->email = $postData['email'];

        if($site->save())
            return response()->json(['status' => true, 'code' => 200, 'data' => $site, 'msg' => 'Location updated successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getSite(Request $request) {

        $postData = $request->post();
        if(empty($postData['user_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, user_id is required!']);

        $sites = Site::where('user_id', $postData['user_id'])->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Location retrieved successfully!', 'data' => $sites]);
    }

    public function deleteSite(Request $request) {

        $postData = $request->post();
        if(empty($postData['location_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, location_id is required!']);

        $result = Site::where('id', $postData['location_id'])->delete();
        if($result)
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Location removed successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function addAccount(Request $request) {

        $postData = $request->post();
        if(empty($postData['site_id'])) {
            // No site_id has passed, so this must be the new site case
            // Check for required params in case of adding new site
            $requiredFields = ['user_id', 'title', 'lat', 'lng', 'address'];
            $validated = validateData($requiredFields, $postData);
            if(!$validated['status'])
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Site creation error: '.$validated['error']]);

            $siteArr = array(
                'user_id' => $postData['user_id'],
                'title' => $postData['title'],
                'lat' => $postData['lat'],
                'lng' => $postData['lng'],
                'address' => $postData['address']
            );
            if(!empty($postData['email']))
                $siteArr['email'] = $postData['email'];

            $site = Site::create($siteArr);
            if($site)
                $postData['site_id'] = $site->id;
            else
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong while creating new site!']);
        }

        $requiredFields = ['user_id', 'site_id', 'account_name', 'account_number', 'optional_information'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $accArr = array(
            'site_id' => $postData['site_id'],
            'account_name' => $postData['account_name'],
            'account_number' => $postData['account_number'],
            'optional_information' => $postData['optional_information']
        );

        $res = Account::create($accArr);
        if($res) {
            // Check if user has provided fixed-costs or not
            if(!empty($postData['fixed_cost'])) {
                $fixedCostArr = [];
                $n = 0;
                foreach($postData['fixed_cost'] as $fixedCost) {
                    $fixedCostArr[$n]['account_id'] = $res->id;
                    $fixedCostArr[$n]['title'] = $fixedCost['name'];
                    $fixedCostArr[$n]['value'] = $fixedCost['value'];
                    $fixedCostArr[$n]['added_by'] = $postData['user_id'];
                    $fixedCostArr[$n]['created_at'] = date("Y-m-d H:i:s");
                    if(isset($fixedCost['is_active']))
                        $fixedCostArr[$n]['is_active'] = $postData['is_active'];
                    $n++;
                }
                FixedCost::insert($fixedCostArr);
            }
            // Check if there are any default fixed costs
            if(!empty($postData['default_fixed_cost'])) {
                $d = 0;
                $defaultCostArr = [];
                foreach ($postData['default_fixed_cost'] as $defaultCost) {
                    $defaultCostArr[$d]['account_id'] = $res->id;
                    $defaultCostArr[$d]['fixed_cost_id'] = $defaultCost['id'];
                    $defaultCostArr[$d]['value'] = $defaultCost['value'];
                    $defaultCostArr[$d]['created_at'] = date("Y-m-d H:i:s");
                    if(isset($defaultCost['is_active']))
                        $defaultCostArr[$d]['is_active'] = $defaultCost['is_active'];

                    $d++;
                }
                AccountFixedCost::insert($defaultCostArr);
            }

            $data = Account::with(['fixedCosts', 'defaultFixedCosts'])->find($res->id);

            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account added successfully!', 'data' => $data]);
        }

        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function updateAccount(Request $request) {

        $postData = $request->post();
        if(empty($postData['site_id'])) {
            // No site_id has passed, so this must be the new site case
            // Check for required params in case of adding new site
            $requiredFields = ['user_id', 'title', 'lat', 'lng', 'address'];
            $validated = validateData($requiredFields, $postData);
            if(!$validated['status'])
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Site creation error: '.$validated['error']]);

            $siteArr = array(
                'user_id' => $postData['user_id'],
                'title' => $postData['title'],
                'lat' => $postData['lat'],
                'lng' => $postData['lng'],
                'address' => $postData['address']
            );
            if(!empty($postData['email']))
                $siteArr['email'] = $postData['email'];

            $site = Site::create($siteArr);
            if($site)
                $postData['site_id'] = $site->id;
            else
                return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong while creating new site!']);
        }

        $requiredFields = ['account_id', 'user_id', 'site_id', 'account_name', 'account_number', 'optional_information'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $account = Account::find($postData['account_id']);
        if(empty($account))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, wrong account_id is provided!']);

        $account->site_id = $postData['site_id'];
        $account->account_name = $postData['account_name'];
        $account->account_number = $postData['account_number'];
        $account->optional_information = $postData['optional_information'];

        if($account->save()) {
            // Check if user has deleted any fixed costs or not
            if(!empty($postData['removed_fixed_costs_ids'])) {
                $removedCostIDs = $postData['removed_fixed_costs_ids'];
                foreach($removedCostIDs as $removedCostID)
                    FixedCost::where('id', $removedCostID)->delete();
            }

            // Check if user has provided fixed-costs or not
            if(!empty($postData['fixed_cost'])) {
                foreach($postData['fixed_cost'] as $fixedCost) {

                    $fixedCostArr = array(
                        'account_id' => $account->id,
                        'title' => $fixedCost['name'],
                        'value' => $fixedCost['value'],
                        'added_by' => $postData['user_id'],
                        'created_at' => date("Y-m-d H:i:s")
                    );
                    if(isset($fixedCost['is_active']))
                        $fixedCostArr['is_active'] = $fixedCost['is_active'];

                    if(!empty($fixedCost['id']))
                        FixedCost::where('id', $fixedCost['id'])->update($fixedCostArr);
                    else
                        FixedCost::create($fixedCostArr);
                }
            }
            // Check if there are any default fixed costs
            if(!empty($postData['default_fixed_cost'])) {
                foreach ($postData['default_fixed_cost'] as $defaultCost) {
                    if(empty($defaultCost['id']))
                        continue;

                    $upd = array(
                        'value' => $defaultCost['value']
                    );

                    if(isset($defaultCost['is_active']))
                        $upd['is_active'] = $defaultCost['is_active'];

                    AccountFixedCost::where('id', $defaultCost['id'])->update($upd);
                }
            }

            $data = Account::with(['fixedCosts', 'defaultFixedCosts'])->find($account->id);

            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account updated successfully!', 'data' => $data]);
        }

        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getAccounts(Request $request) {

        $accID = $request->get('account_id');
        if(empty($accID))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);

        // $data = Account::with('site')->where('site_id', $siteID)->get();
        $data = Account::where('id', $accID)->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account retrieved successfully!', 'data' => $data]);
    }

    public function deleteAccount(Request $request) {

        $postData = $request->post();
        if(empty($postData['account_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);

        $result = Account::where('id', $postData['account_id'])->delete();
        if($result)
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Account removed successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getAllData(Request $request) {

        // Get sites, accounts, fixedCosts
        if(empty($request->get('user_id')))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, user_id is required!']);

        $userID = $request->get('user_id');
        $defaultCosts = FixedCost::where('is_default', 1)->get();

        $data = Site::with(['account.fixedCosts', 'account.defaultFixedCosts.fixedCost'])->where('user_id', $userID)->get();
        foreach ($data as $site) {
            foreach ($site->account as $account) {
                if(count($account->defaultFixedCosts) == 0 && count($defaultCosts) > 0) {
                    // Looks like account default cost relationship has not been set yet
                    $arr = [];
                    foreach ($defaultCosts as $defaultCost) {
                        $arr = array(
                            'account_id' => $account->id,
                            'fixed_cost_id' => $defaultCost->id,
                            'is_active' => 1
                        );
                        AccountFixedCost::create($arr);
                    }
                }
                $account->refresh();
            }
        }

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Data retrieved successfully!', 'data' => $data]);
    }

    public function addMeter(Request $request) {

        $postData = $request->post();
        $requiredFields = ['account_id', 'meter_type_id', 'meter_title', 'meter_number', 'meter_reading_date', 'meter_reading'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $meterArr = array(
            'account_id' => $postData['account_id'],
            'meter_type_id' => $postData['meter_type_id'],
            'meter_title' => $postData['meter_title'],
            'meter_number' => $postData['meter_number']
        );

        $res = Meter::create($meterArr);
        if($res) {
            // Add meter reading in system
            $meterReading = array(
                'meter_id' => $res->id,
                //'reading_date' => date('Y-m-d', strtotime($postData['meter_reading_date'])),
                'reading_date' => $postData['meter_reading_date'],
                'reading_value' => $postData['meter_reading']
            );
            MeterReadings::create($meterReading);
            $data = Meter::with('readings')->find($res->id);

            return response()->json(['status' => true, 'code' => 200, 'data' => $data, 'msg' => 'Meter added successfully!']);
        }
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function addFixedCost(Request $request) {

        $postData = $request->post();
        $requiredFields = ['account_id', 'user_id'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        if(!empty($postData['fixed_cost'])) {
            foreach ($postData['fixed_cost'] as $fixedCost) {

                if(empty($fixedCost['title']) || empty($fixedCost['value']))
                    continue;

                $costArr['account_id'] = $postData['account_id'];
                $costArr['title'] = $fixedCost['title'];
                $costArr['value'] = $fixedCost['value'];
                $costArr['added_by'] = $postData['user_id'];
                if(isset($fixedCost['is_active']))
                    $costArr['is_active'] = $fixedCost['is_active'];

                if(empty($fixedCost['id']))
                    FixedCost::create($costArr);
                else
                    FixedCost::where('id', $fixedCost['id'])->update($costArr);
            }
        }

        // Check if user has deleted any fixed costs or not
        if(!empty($postData['removed_fixed_costs_ids'])) {
            $removedCostIDs = $postData['removed_fixed_costs_ids'];
            foreach($removedCostIDs as $removedCostID)
                FixedCost::where('id', $removedCostID)->delete();
        }

        // Get all fixed costs for the account
        $res = Account::with('fixedCosts')->where('id', $postData['account_id'])->get();
        if($res)
            return response()->json(['status' => true, 'code' => 200, 'data' => $res, 'msg' => 'Fixed cost added successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function addDefaultCost(Request $request) {

        $postData = $request->post();
        if(empty($postData['account_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);

        if(!empty($postData['default_fixed_cost'])) {
            foreach ($postData['default_fixed_cost'] as $defaultCost) {
                if(empty($defaultCost['id']))
                    continue;

                $upd = [];
                if(!empty($defaultCost['value']))
                    $upd['value'] = $defaultCost['value'];

                if(isset($defaultCost['is_active']))
                    $upd['is_active'] = $defaultCost['is_active'];

                if(!empty($upd))
                    AccountFixedCost::where('id', $defaultCost['id'])->update($upd);
            }
        }

        // Get all fixed costs for the account
        $res = AccountFixedCost::where('account_id', $postData['account_id'])->get();
        if($res)
            return response()->json(['status' => true, 'code' => 200, 'data' => $res, 'msg' => 'Default cost added successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function updateMeter(Request $request) {

        $postData = $request->post();
        $requiredFields = ['meter_id', 'meter_reading_id', 'account_id', 'meter_type_id', 'meter_title', 'meter_number', 'meter_reading_date', 'meter_reading'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $meter = Meter::find($postData['meter_id']);
        $meterReading = MeterReadings::find($postData['meter_reading_id']);
        if(empty($meter) || empty($meterReading))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, wrong meter_id or meter_reading_id provided!']);

        $meter->account_id = $postData['account_id'];
        $meter->meter_type_id = $postData['meter_type_id'];
        $meter->meter_title = $postData['meter_title'];
        $meter->meter_number = $postData['meter_number'];

        if($meter->save()) {

            //$meterReading->reading_date = date('Y-m-d', strtotime($postData['meter_reading_date']));
            $meterReading->reading_date = $postData['meter_reading_date'];
            $meterReading->reading_value = $postData['meter_reading'];
            $meterReading->save();

            $data = Meter::with('readings')->find($meterReading->id);

            return response()->json(['status' => true, 'code' => 200, 'data' => $data, 'msg' => 'Meter updated successfully!']);
        }
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getMeter(Request $request) {

        $accountID = $request->get('account_id');
        if(empty($accountID))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);

        $meters = Meter::with('readings')->where('account_id', $accountID)->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meters retrieved successfully!', 'data' => $meters]);
    }

    public function getMeterReadings(Request $request) {

        $meterID = $request->get('meter_id');
        if(empty($meterID))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, meter_id is required!']);

        $meters = Meter::with('readings')->where('id', $meterID)->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meters retrieved successfully!', 'data' => $meters]);
    }

    public function addMeterReadings(Request $request) {

        $postData = $request->post();
        $requiredFields = ['meter_id', 'meter_reading_date', 'meter_reading'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $siteArr = array(
            'meter_id' => $postData['meter_id'],
            'reading_date' => $postData['meter_reading_date'],
            'reading_value' => $postData['meter_reading']
        );

        $res = MeterReadings::create($siteArr);
        if($res)
            return response()->json(['status' => true, 'code' => 200, 'data' => $res, 'msg' => 'Meter readings added successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function deleteMeter(Request $request) {

        $postData = $request->post();
        if(empty($postData['meter_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, location_id is required!']);

        $result = Meter::where('id', $postData['meter_id'])->delete();
        if($result)
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meter removed successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function deleteMeterReading(Request $request) {

        $postData = $request->post();
        if(empty($postData['reading_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, location_id is required!']);

        $result = MeterReadings::where('id', $postData['reading_id'])->delete();
        if($result)
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meter reading removed successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getMeterTypes(Request $request) {

        $meters = MeterType::all();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Meters retrieved successfully!', 'data' => $meters]);
    }

    public function verifyEmail(Request $request) {

        $postData = $request->post();
        if(empty($postData['email']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, email field is required!']);

        // Now check if this email exists in our system or not
        $result = User::where('email', $postData['email'])->get();
        if(count($result) == 0)
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'No user exists under this email in our system!']);

        // Now that email exists in our system, make a reset code
        $digits = 5;
        $code = rand(pow(10, $digits-1), pow(10, $digits)-1);

        // Add this code into the database column
        $upd = User::where('email', $postData['email'])->update(['password_reset_code' => $code]);
        if(!$upd)
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);

        // Send the code to user email
        $from = 'lightsandwater01@gmail.com';
        $fromName = 'LightsAndWater';
        $to_email = $postData['email'];
        $to_name = $to_email;
        $data = ['from_name' => 'Lights And Water','body' => "Hi, we have received you password reset request. Please use the following code: ". $code];
        Mail::send('forget_password_mail', $data, function($message) use ($to_name, $to_email) {
        $message->to($to_email, $to_name)
        ->subject('LightsAndWater - Password reset request');
        $message->from('lightsandwater01@gmail.com', 'LightsAndWater');
        });

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'A code has been sent to your email.!']);
    }

    public function verifyCode(Request $request) {

        $postData = $request->post();
        if(empty($postData['code']) || empty($postData['email']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, code and email fields are required!']);

        $user = User::where(['email' => $postData['email'], 'code' => $postData['code']])->get();
        if(count($user) == 1) {
            User::where(['email' => $postData['email'], 'code' => $postData['code']])->update(['code' => null]);
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Code matched. Please proceed!']);
        }
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Code did not matched!']);
    }

    public function resetPassword(Request $request) {

        $postData = $request->post();
        if(empty($postData['password']) || empty($postData['email']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, password and email fields are required!']);

        $user = User::where(['email' => $postData['email']])->get();
        if(count($user) == 1) {
            User::where(['email' => $postData['email']])->update(['password' => bcrypt($postData['password'])]);
            return response()->json(['status' => true, 'code' => 200, 'msg' => 'Code matched. Please proceed!']);
        }
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Wrong email provided!']);
    }

    public function updateMeterReadings(Request $request) {

        $postData = $request->post();
        $requiredFields = ['meter_reading_id', 'meter_id', 'meter_reading_date', 'meter_reading'];
        $validated = validateData($requiredFields, $postData);
        if(!$validated['status'])
            return response()->json(['status' => false, 'code' => 400, 'msg' => $validated['error']]);

        $reading = MeterReadings::find($postData['meter_reading_id']);
        if(empty($reading))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, wrong meter_reading_id provided!']);

        $reading->meter_id = $postData['meter_id'];
        $reading->reading_date = $postData['meter_reading_date'];
        $reading->reading_value = $postData['meter_reading'];

        if($reading->save())
            return response()->json(['status' => true, 'code' => 200, 'data' => $reading, 'msg' => 'Meter readings updated successfully!']);
        else
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, something went wrong!']);
    }

    public function getTC() {

        $settings = Settings::first();
        $data = ['id' => $settings->id, 'terms_condition' => $settings->terms_condition];
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Terms and conditions retrieved successfully!', 'data' => $data]);
    }

    public function getAds() {

        $ads = Ads::with('category')->get();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Ads retrieved  successfully!', 'data' => $ads]);
    }

    public function getFixedCosts(Request $request) {

        $postData = $request->post();
        if(empty($postData['account_id']))
            return response()->json(['status' => false, 'code' => 400, 'msg' => 'Oops, account_id is required!']);

        $fixedCosts = FixedCost::with('account')->where('account_id', $postData['account_id'])
            ->where('is_default', 0)->get();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Fixed costs retrieved  successfully!', 'data' => $fixedCosts]);
    }

    public function getDefaultCosts() {

        $defaultCosts = FixedCost::select('id','account_id','title','value','is_default','created_at')->where('is_default', 1)->get();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Default costs retrieved  successfully!', 'data' => $defaultCosts]);
    }

    public function getAdsCategories(Request $request) {

        $postData = $request->post();
        if(!empty($postData['category_id']))
            $categories = AdsCategory::with('ads')->where('id', $postData['category_id'])->get();
        else
            $categories = AdsCategory::with('ads')->get();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Ads categories retrieved successfully!', 'data' => $categories]);
    }

    public function getAlarms(Request $request) {

        $postData = $request->post();
        $whereArr = [];
        if(!empty($postData['date']))
            $whereArr['date'] = date("Y-m-d", strtotime($postData['date']));

        if(!empty($postData['region_id']))
            $whereArr['region_id'] = $postData['region_id'];

        if(!empty($whereArr))
            $alarms = RegionAlarms::where($whereArr)->get();
        else
            $alarms = RegionAlarms::all();

        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Alarms retrieved successfully!', 'data' => $alarms]);
    }

    public function getRegions() {

        $regions = Regions::all();
        return response()->json(['status' => true, 'code' => 200, 'msg' => 'Regions retrieved  successfully!', 'data' => $regions]);
    }
}
