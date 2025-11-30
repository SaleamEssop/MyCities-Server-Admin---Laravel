<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountFixedCost;
use App\Models\AccountType;
use App\Models\Ads;
use App\Models\AdsCategory;
use App\Models\FixedCost;
use App\Models\Meter;
use App\Models\MeterCategory;
use App\Models\MeterReadings;
use App\Models\MeterType;
use App\Models\RegionAlarms;
use App\Models\RegionCosts;
use App\Models\Regions;
use App\Models\Settings;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['email']) || empty($postData['password']))
            return redirect()->back();

        // Verify credentials
        $user = User::where('email', $postData['email'])->first();
        if (empty($user)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, wrong email provided!');
            return redirect()->back();
        }

        // Now check the password hash
        $dbPasswordHash = $user->password;
        $userPassword = $postData['password'];
        if (!Hash::check($userPassword, $dbPasswordHash)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, wrong credentials provided!');
            return redirect()->back();
        }

        // Now check if incoming user is admin or not
        if (!$user->is_admin) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, you are not authorized to access admin panel!');
            return redirect()->back();
        }

        Auth::login($user);

        return redirect()->intended('admin/');
    }

    public function showUsers(Request $request)
    {
        $adminID = Auth::user()->id;
        if (Auth::user()->is_super_admin)
            $users = User::whereNotIn('id', [$adminID])->get();
        else
            $users = []; // Because this admin should not have any users under it; this could be changed in the future

        return view('admin.users', ['users' => $users]);
    }

    public function showAccounts(Request $request)
    {
        if (Auth::user()->is_super_admin)
            $accounts = Account::with('site')->get();
        else
            $accounts = []; // Because this admin should not have any users under it; this could be changed in the future

        return view('admin.accounts', ['accounts' => $accounts]);
    }

    public function showSites(Request $request)
    {
        if (Auth::user()->is_super_admin)
            $sites = Site::with(['user', 'region'])->get();
        else
            $sites = []; // Because this admin should not have any users under it; this could be changed in the future

        return view('admin.sites', ['sites' => $sites]);
    }

    public function showRegions(Request $request)
    {
        if (Auth::user()->is_super_admin)
            $regions = Regions::all();
        else
            $regions = []; // Because this admin should not have any users under it; this could be changed in the future

        return view('admin.regions', ['regions' => $regions]);
    }

    public function addUserForm(Request $request)
    {
        return view('admin.create_user');
    }

    public function createUser(Request $request)
    {
        $postData = $request->post();
        $alreadyExists = User::where('email', $postData['email'])->get();
        if (count($alreadyExists) > 0) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, user with this email already exists.');
            return redirect()->back()->withInput();
        }
        $userArr = array(
            'name' => $postData['name'],
            'email' => $postData['email'],
            'contact_number' => $postData['contact_number'],
            'password' => bcrypt($postData['password'])
        );
        $result = User::create($userArr);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'User added successfully!');
            return redirect('admin/users');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteUser(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        DB::beginTransaction();
        try {
            $deleted = User::where('id', $id)->first()->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            /*print_r($e->getMessage());
            print_r($e->getFile());
            print_r($e->getLine());
            print_r($e->getTraceAsString());*/
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! User deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editUserForm(Request $request, $id)
    {
        $user = User::find($id);
        return view('admin.edit_user', ['user' => $user]);
    }

    public function editUser(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['user_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, user ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'name' => $postData['name'],
            'contact_number' => $postData['contact_number'],
            'email' => $postData['email']
        );
        if (!empty($postData['password']))
            $updArr['password'] = bcrypt($postData['password']);

        $updated = User::where('id', $postData['user_id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! User updated successfully!');
            return redirect('admin/users');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function addSiteForm(Request $request)
    {
        $adminID = Auth::user()->id;
        $users = User::whereNotIn('id', [$adminID])->get();
        $regions = Regions::all();
        return view('admin.create_site', ['users' => $users, 'regions' => $regions]);
    }

    public function createSite(Request $request)
    {
        $postData = $request->post();
        $siteArr = array(
            'region_id' => $postData['region_id'],
            'user_id' => $postData['user_id'],
            'title' => $postData['title'],
            'lat' => $postData['lat'],
            'lng' => $postData['lng'],
            'email' => $postData['email'],
            'address' => $postData['address'],
            'billing_type' => $postData['billing_type'] ?? 'monthly',
            'site_username' => $postData['site_username'] ?? null
        );
        if (!empty($postData['site_password'])) {
            $siteArr['site_password'] = Hash::make($postData['site_password']);
        }
        $result = Site::create($siteArr);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Site created successfully!');
            return redirect('admin/sites');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function createRegion(Request $request)
    {
        $postData = $request->post();
        $region = Regions::create([
            'name' => $postData['region_name'],
            'water_email' => $postData['water_email'],
            'electricity_email' => $postData['electricity_email'],
        ]);
        if ($region) {
            // Check for cost builders
            if (!empty($postData['water_cost_min']) && count($postData['water_cost_min']) > 0) {
                $costArr = [];
                for ($i = 0; $i < count($postData['water_cost_min']); $i++) {
                    $costArr[$i]['meter_type_id'] = $postData['water_type_id'];
                    $costArr[$i]['region_id'] = $region->id;
                    $costArr[$i]['min'] = $postData['water_cost_min'][$i];
                    $costArr[$i]['max'] = $postData['water_cost_max'][$i];
                    $costArr[$i]['amount'] = $postData['water_cost_amount'][$i];
                    $costArr[$i]['created_at'] = date('Y-m-d H:i:s');
                }
                RegionCosts::insert($costArr);
            }

            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Region created successfully!');
            return redirect('admin/regions');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteSite(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        DB::beginTransaction();
        try {
            $deleted = Site::where('id', $id)->first()->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Site deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function addAccountForm(Request $request)
    {
        $users = User::where(['is_admin' => 0, 'is_super_admin' => 0])->get();
        $defaultCosts = FixedCost::where('is_default', 1)->get();
        return view('admin.create_account', ['users' => $users, 'defaultCosts' => $defaultCosts]);
    }

    public function getUserSites(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['user'])) {
            echo json_encode(['status' => 400, 'msg' => 'User_id is required!']);
            return;
        }

        $sites = Site::where('user_id', $postData['user'])->get(['id', 'title']);
        return json_encode(['status' => 200, 'details' => $sites, 'msg' => 'Sites retrieved successfully!']);
    }

    public function createAccount(Request $request)
    {
        $postData = $request->post();
        $accArr = array(
            'site_id' => $postData['site_id'],
            'account_name' => $postData['title'],
            'account_number' => $postData['number']
        );

        $exists = Account::where($accArr)->first();
        if (!empty($exists)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, account with same information already exists.');
            return redirect()->back();
        }

        $accArr['billing_date'] = $postData['billing_date'] ?? null;
        $accArr['optional_information'] = $postData['optional_info'] ?? null;

        $result = Account::create($accArr);
        if ($result) {

            // Add default cost
            if (!empty($postData['default_cost_value']) && $postData['default_ids']) {
                $accountDefaultCosts = [];
                for ($i = 0; $i < count($postData['default_ids']); $i++) {
                    $accountDefaultCosts[$i]['account_id'] = $result->id;
                    $accountDefaultCosts[$i]['fixed_cost_id'] = $postData['default_ids'][$i];
                    $accountDefaultCosts[$i]['value'] = $postData['default_cost_value'][$i];
                    $accountDefaultCosts[$i]['created_at'] = date('Y-m-d H:i:s');
                }
                if (!empty($accountDefaultCosts))
                    AccountFixedCost::insert($accountDefaultCosts);
            }

            if (!empty($postData['additional_cost_name'])) {
                $fixedCostArr = [];
                for ($i = 0; $i < count($postData['additional_cost_name']); $i++) {
                    $fixedCostArr[$i]['account_id'] = $result->id;
                    $fixedCostArr[$i]['title'] = $postData['additional_cost_name'][$i];
                    $fixedCostArr[$i]['value'] = $postData['additional_cost_value'][$i];
                    $fixedCostArr[$i]['added_by'] = Auth::user()->id;
                    $fixedCostArr[$i]['created_at'] = date("Y-m-d H:i:s");
                }
                FixedCost::insert($fixedCostArr);
            }

            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Account created successfully!');
            return redirect('admin/accounts');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteAccount(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        // In next phase delete all the related models as well.
        DB::beginTransaction();
        try {
            $deleted = Account::where('id', $id)->first()->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Account deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editSiteForm(Request $request, $id)
    {
        $adminID = Auth::user()->id;
        $users = User::whereNotIn('id', [$adminID])->get();
        $site = Site::find($id);
        $regions = Regions::all();
        return view('admin.edit_site', ['site' => $site, 'users' => $users, 'regions' => $regions]);
    }

    public function editAdForm(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, ad ID is required.');
            return redirect()->back();
        }
        $ad = Ads::with('category')->find($id);
        $categories = AdsCategory::all();
        return view('admin.edit_ads', ['ad' => $ad, 'categories' => $categories]);
    }

    public function editSite(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['site_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, site ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'region_id' => $postData['region_id'],
            'user_id' => $postData['user_id'],
            'title' => $postData['title'],
            'lat' => $postData['lat'],
            'lng' => $postData['lng'],
            'address' => $postData['address'],
            'email' => $postData['email'],
            'billing_type' => $postData['billing_type'] ?? 'monthly',
            'site_username' => $postData['site_username'] ?? null
        );

        if (!empty($postData['site_password'])) {
            $updArr['site_password'] = Hash::make($postData['site_password']);
        }

        $updated = Site::where('id', $postData['site_id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Site updated successfully!');
            return redirect('admin/sites');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editAd(Request $request)
    {
        $postData = $request->post();      
        if (empty($postData['ad_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, ad ID is required.');
            return redirect('/admin/ads')->back();
        }

        $path = '';
        if ($request->hasFile('ad_image'))
            $path = $request->file('ad_image')->store('public/ads');

        $ad = Ads::find($postData['ad_id']);
        if (empty($ad)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect('/admin/ads/');
        }

        $ad->ads_category_id = $postData['ads_category_id'];
        $ad->name = $postData['ad_name'];
        $ad->url = $postData['ad_url'];
        $ad->price = $postData['ad_price'];
        $ad->priority = $postData['ad_priority'];
        $ad->description = $postData['description-editor'];

        if (!empty($path))
            $ad->image = $path;
           
        if ($ad->save()) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Ad updated successfully!');
            return redirect('/admin/ads/');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editAccountForm(Request $request, $id)
    {
        $users = User::where(['is_admin' => 0, 'is_super_admin' => 0])->get();
        $account = Account::with(['fixedCosts', 'site.user', 'defaultFixedCosts.fixedCost'])->find($id);
        $sites = Site::where('user_id', $account->site->user->id)->get();
        $defaultCosts = FixedCost::where('is_default', 1)->get();

        // Manage default costs
        // Assign any default cost if not assigned to this account already

        $accountDefaultCosts = [];
        foreach ($account->defaultFixedCosts as $accountCosts) {
            $accountDefaultCosts[] = $accountCosts->fixed_cost_id;
        }

        foreach ($defaultCosts as $defaultCost) {
            // Check if this default cost is assigned to this account or not
            if (in_array($defaultCost->id, $accountDefaultCosts)) // Continue as it exists already
                continue;

            // Now add this default cost in this account as well.
            $arr = array(
                'account_id' => $id,
                'fixed_cost_id' => $defaultCost->id,
                'is_active' => 1,
            );
            AccountFixedCost::create($arr);
            $account->refresh();
        }

        return view('admin.edit_account', ['account' => $account, 'sites' => $sites, 'users' => $users]);
    }

    public function editAccount(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['account_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, account ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'site_id' => $postData['site_id'],
            'account_name' => $postData['title'],
            'account_number' => $postData['number']
        );

        $exists = Account::where($updArr)->where('id', '<>', $postData['account_id'])->first();
        if (!empty($exists)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, account with same information already exists.');
            return redirect()->back();
        }

        $updArr['billing_date'] = $postData['billing_date'] ?? null;
        $updArr['optional_information'] = $postData['optional_info'] ?? null;

        $updated = Account::where('id', $postData['account_id'])->update($updArr);

        // Check for account default costs here
        if (!empty($postData['default_ids']) && !empty($postData['default_cost_value'])) {
            for ($i = 0; $i < count($postData['default_ids']); $i++) {
                AccountFixedCost::where('id', $postData['default_ids'][$i])->update(['value' => $postData['default_cost_value'][$i]]);
            }
        }

        // Check for fixed costs as well
        if (!empty($postData['additional_cost_name'])) {
            for ($i = 0; $i < count($postData['additional_cost_name']); $i++) {

                if ($postData['fixed_cost_type'][$i] == 'old') { // Update case
                    $costArr = array(
                        'title' => $postData['additional_cost_name'][$i],
                        'value' => $postData['additional_cost_value'][$i]
                    );
                    FixedCost::where('id', $postData['fixed_cost_id'][$i])->update($costArr);
                } elseif ($postData['fixed_cost_type'][$i] == 'new') { // Update case
                    $costArr = array(
                        'account_id' => $postData['account_id'],
                        'title' => $postData['additional_cost_name'][$i],
                        'value' => $postData['additional_cost_value'][$i],
                        'added_by' => Auth::user()->id,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    FixedCost::create($costArr);
                }
            }
        }

        // Process the deleted costs
        if (!empty($postData['deleted'])) {
            $deletedArr = explode(',', $postData['deleted']);
            $deletedArr = array_filter($deletedArr);
            if (!empty($deletedArr)) {
                foreach ($deletedArr as $deletedID) {
                    FixedCost::where('id', $deletedID)->delete();
                }
            }
        }

        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Account    updated successfully!');
            return redirect('admin/accounts');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function showMeters(Request $request)
    {
        $meters = Meter::with(['meterTypes', 'account', 'meterCategory'])->get();
        return view('admin.meters', ['meters' => $meters]);
    }

    public function deleteMeter(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        // In next phase delete all the related models as well.
        DB::beginTransaction();
        try {
            $deleted = Meter::where('id', $id)->first()->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Meter deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function addMeterForm(Request $request)
    {
        $accounts = Account::all();
        $meterTypes = MeterType::all();
        $meterCats = MeterCategory::all();
        return view('admin.create_meter', ['accounts' => $accounts, 'meterTypes' => $meterTypes, 'meterCats' => $meterCats]);
    }

    public function createMeter(Request $request)
    {
        $postData = $request->post();
        $meterArr = array(
            'account_id' => $postData['account_id'],
            'meter_category_id' => $postData['meter_cat_id'],
            'meter_type_id' => $postData['meter_type_id'],
            'meter_title' => $postData['title'],
            'meter_number' => $postData['number']
        );

        $exists = Meter::where($meterArr)->first();
        if (!empty($exists)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, meter with same information already exists.');
            return redirect()->back();
        }

        $result = Meter::create($meterArr);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Meter created successfully!');
            return redirect('admin/meters');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editMeterForm(Request $request, $id)
    {
        $accounts = Account::all();
        $meterCats = MeterCategory::all();
        $meterTypes = MeterType::all();
        $meter = Meter::find($id);
        return view('admin.edit_meter', ['accounts' => $accounts, 'meterTypes' => $meterTypes, 'meter' => $meter, 'meterCats' => $meterCats]);
    }

    public function editMeter(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['meter_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, meter ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'account_id' => $postData['account_id'],
            'meter_category_id' => $postData['meter_cat_id'],
            'meter_type_id' => $postData['meter_type_id'],
            'meter_title' => $postData['title'],
            'meter_number' => $postData['number']
        );

        $exists = Meter::where($updArr)->where('id', '<>', $postData['meter_id'])->first();
        if (!empty($exists)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, meter with same information already exists.');
            return redirect()->back();
        }

        $updated = Meter::where('id', $postData['meter_id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Meter updated successfully!');
            return redirect('admin/meters');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function showMeterReadings(Request $request)
    {
        $meterReadings = MeterReadings::with('meter')->get();
        return view('admin.meter_readings', ['meterReadings' => $meterReadings]);
    }

    public function showAdsCategories(Request $request)
    {
        
        $categories = AdsCategory::with('child_display')->get();
        $parent_categories = AdsCategory::whereNull('parent_id')->pluck('name','id')->toArray();
        
        return view('admin.ads_categories', ['categories' => $categories,'parent_categories' => $parent_categories]);
    }

    public function showAds(Request $request)
    {
        $ads = Ads::with('category')->get();
        $categories = AdsCategory::all();
        return view('admin.ads', ['ads' => $ads, 'categories' => $categories]);
    }

    public function addMeterReadingForm(Request $request)
    {
        $meters = Meter::all();
        return view('admin.create_meter_reading', ['meters' => $meters]);
    }

    public function createMeterReading(Request $request)
    {
        $postData = $request->post();
        $readingImg = null;
        // Check if meter reading image is provided
        if ($request->hasFile('reading_image'))
            $readingImg = $request->file('reading_image')->store('public/readings');
        $meterArr = array(
            'meter_id' => $postData['meter_id'],
            'reading_date' => $postData['reading_date'],
            'reading_value' => $postData['reading_value'],
            'reading_image' => $readingImg
        );
        $result = MeterReadings::create($meterArr);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Meter reading added successfully!');
            return redirect('/admin/meter-readings');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function createAdCategory(Request $request)
    {

        $postData = $request->post();
        
        $category = array(
            'name' => $postData['category_name'],
            'parent_id' => $postData['parent_id'] ?? null,
        );
        $categoryExists = AdsCategory::where('name', strtolower($postData['category_name']))->first();
        if (!empty($categoryExists)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, category with same name already exists.');
            return redirect()->back();
        }

        $result = AdsCategory::create($category);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Category added successfully!');
            return redirect('/admin/ads/categories');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function createAd(Request $request)
    {
        $postData = $request->post();
        $path = '';
        if ($request->hasFile('ad_image'))
            $path = $request->file('ad_image')->store('public/ads');

        $ad = array(
            'ads_category_id' => $postData['ads_category_id'],
            'name' => $postData['ad_name'],
            'image' => $path,
            'url' => $postData['ad_url'],
            'price' => $postData['ad_price'],
            'priority' => $postData['ad_priority'],
            'description' => $postData['description-editor']
        );

        $result = Ads::create($ad);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Category added successfully!');
            return redirect('/admin/ads/');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteMeterReading(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        $deleted = MeterReadings::where('id', $id)->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Meter reading removed successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editMeterReadingForm(Request $request, $id)
    {
        $meters = Meter::all();
        $meterReading = MeterReadings::find($id);
        return view('admin.edit_meter_reading', ['meters' => $meters, 'meterReading' => $meterReading]);
    }

    public function editMeterReading(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['meter_reading_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, reading ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'meter_id' => $postData['meter_id'],
            'reading_date' => $postData['reading_date'],
            'reading_value' => $postData['reading_value']
        );

        $updated = MeterReadings::where('id', $postData['meter_reading_id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Meter reading updated successfully!');
            return redirect('admin/meter-readings');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function showDefaultCosts(Request $request)
    {
        $defaultCosts = FixedCost::where('is_default', 1)->get();
        return view('admin.default-costs', ['defaultCosts' => $defaultCosts]);
    }

    public function showAlarms(Request $request)
    {
        $regionAlarms = RegionAlarms::with('region')->get();
        $regions = Regions::all();
        return view('admin.alarms', ['regionAlarms' => $regionAlarms, 'regions' => $regions]);
    }

    public function deleteAlarm(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        // In next phase delete all the related models as well.
        $deleted = RegionAlarms::where('id', $id)->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Alarm removed successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteDefaultCost(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        $deleted = FixedCost::where('id', $id)->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Default cost removed successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function createDefaultCost(Request $request)
    {
        $postData = $request->post();
        $costArr = array(
            'title' => $postData['cost_name'],
            'value' => $postData['cost_value'],
            'is_default' => 1
        );
        $result = FixedCost::create($costArr);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Default cost created successfully!');
            return redirect('admin/default-costs');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function createAlarm(Request $request)
    {
        $postData = $request->post();
        $alarmArr = array(
            'region_id' => $postData['region_id'],
            'date' => $postData['alarm_date'],
            'time' => $postData['alarm_time'],
            'message' => $postData['alarm_message'],
            'added_by' => Auth::user()->id
        );

        $result = RegionAlarms::create($alarmArr);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Alarm created successfully!');
            return redirect('admin/alarms');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editDefaultCost(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['default_cost_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        $updArr = array(
            'title' => $postData['cost_name'],
            'value' => $postData['cost_value']
        );

        $updated = FixedCost::where('id', $postData['default_cost_id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Cost updated successfully!');
            return redirect('admin/default-costs');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editAlarm(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['alarm_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        $updArr = array(
            'region_id' => $postData['region_id'],
            'date' => $postData['alarm_date'],
            'time' => $postData['alarm_time'],
            'message' => $postData['alarm_message']
        );

        $updated = RegionAlarms::where('id', $postData['alarm_id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Cost updated successfully!');
            return redirect('admin/alarms');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editAdCategory(Request $request)
    {
        
        $postData = $request->post();
        if (empty($postData['category_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        // $categoryExists = AdsCategory::where('name', strtolower($postData['category_name']))->first();
        // if (!empty($categoryExists)) {
        //     Session::flash('alert-class', 'alert-danger');
        //     Session::flash('alert-message', 'Oops, category with same name already exists.');
        //     return redirect()->back();
        // }

        $category = array('name' => $postData['category_name'],'parent_id' => $postData['parent_id'] ?? null);

        $updated = AdsCategory::where('id', $postData['category_id'])->update($category);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Category updated successfully!');
            return redirect('admin/ads/categories');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editRegion(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['region_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        $updArr = array(
            'name' => $postData['region_name'],
            'electricity_email' => $postData['electricity_email'] ?? null,
            'water_email' => $postData['water_email'] ?? null,
            'water_base_unit' => $postData['water_base'] ?? null,
            'water_base_unit_cost' => $postData['water_unit'] ?? null,
            'electricity_base_unit' => $postData['elect_base'] ?? null,
            'electricity_base_unit_cost' => $postData['elect_unit'] ?? null,
            'cost' => $postData['region_cost'] ?? null
        );

        $updated = Regions::where('id', $postData['region_id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Region updated successfully!');
            return redirect('admin/regions');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteRegion(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        $deleted = Regions::where('id', $id)->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Region deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }
   
    public function deleteAdsCategory(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        // In next phase delete all the related models as well.

        $deleted = AdsCategory::where('id', $id)->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Category deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteAd(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        // In next phase delete all the related models as well.
        $deleted = Ads::where('id', $id)->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Ad deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function showTC()
    {
        if (!Auth::user()->is_super_admin) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, you are not authorized to access this page.');
            return redirect()->back();
        }
        $settings = Settings::first();
        return view('admin.terms-and-conditions', ['settings' => $settings]);
    }

    public function updateTC(Request $request)
    {
        $adminID = Auth::user()->id;
        if (!Auth::user()->is_super_admin) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, you are not authorized to access this page.');
            return redirect()->back();
        }

        $postData = $request->post();
        if (empty($postData['tc'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, invalid request');
            return redirect()->back();
        }

        if (empty($postData['setting_id']))
            $settings = Settings::create(['terms_condition' => $postData['tc']]);
        else
            $settings = Settings::where('id', $postData['setting_id'])->update(['terms_condition' => $postData['tc']]);

        if ($settings) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Terms and conditions updated successfully!');
            return redirect()->back();
        }

        Session::flash('alert-class', 'alert-danger');
        Session::flash('alert-message', 'Oops, something went wrong!');
        return redirect()->back();
    }

    public function addRegionForm(Request $request)
    {
        $waterType = MeterType::where('title', 'Water')->first();
        $electType = MeterType::where('title', 'Electricity')->first();
        $data = array(
            'water_id' => $waterType->id ?? 0,
            'elect_id' => $electType->id ?? 0
        );

        return view('admin.create_region', ['data' => $data]);
    }

    public function editRegionForm(Request $request, $id)
    {
        $region = Regions::find($id);
        return view('admin.edit_region', ['region' => $region]);
    }

    public function showUserDetails(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, invalid request.');
            return redirect()->back();
        }

        // $userDetails = User::with(['sites', 'sites.account', 'sites.account.meters', 'sites.account.meters.readings'])->where('id', $id)->get();
        $userDetails = User::with(['sites', 'sites.account', 'sites.account.meters', 'sites.account.meters.readings'])->find($id);
        return view('admin.user_details', ['userDetails' => $userDetails]);
    }

    public function showUserDetailsV2(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, invalid request.');
            return redirect()->back();
        }

        // $userDetails = User::with(['sites', 'sites.account', 'sites.account.meters', 'sites.account.meters.readings'])->where('id', $id)->get();
        $userDetails = User::with(['sites', 'sites.account', 'sites.account.meters', 'sites.account.meters.readings'])->find($id);
        return view('admin.user_details_v2', ['userDetails' => $userDetails]);
    }

    public function uploadAdsDescPics(Request $request)
    {
        if ($request->hasFile('upload')) {
            $path = $request->file('upload')->store('public/ads');
            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url = Storage::url($path);
            $requestScheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
            $url = $requestScheme . '://' . $_SERVER['HTTP_HOST'] . $url;
            //$url = URL::to(Storage::url($path));
            $msg = 'Image successfully uploaded';
            $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";

            @header('Content-type: text/html; charset=utf-8');
            echo $response;
        }
    }

    public function uploadTCPics(Request $request)
    {
        if ($request->hasFile('upload')) {
            $path = $request->file('upload')->store('public/tc');
            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url = Storage::url($path);
            $requestScheme = $_SERVER['REQUEST_SCHEME'] ?? 'http';
            $url = $requestScheme . '://' . $_SERVER['HTTP_HOST'] . $url;
            $msg = 'Image successfully uploaded';
            $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";

            @header('Content-type: text/html; charset=utf-8');
            echo $response;
        }
    }
    // Start Account Type Code
    public function showAccountType(Request $request)
    {
        if (Auth::user()->is_super_admin)
            $account_type = AccountType::all();
        else
            $account_type = []; // Because this admin should not have any users under it; this could be changed in the future

        return view('admin.account_type.account_type', ['account_type' => $account_type]);
    }
    public function editAccountType(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        $updArr = array(
            'type' => $postData['name'],
        );

        $updated = AccountType::where('id', $postData['id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Account Type updated successfully!');
            return redirect()->route('account-type-list');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteAccountType(Request $request, $id)
    {
        if (empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        $deleted = AccountType::where('id', $id)->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Account Type deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }
    public function addAccountTypeForm(Request $request)
    {
        $waterType = MeterType::where('title', 'Water')->first();
        $electType = MeterType::where('title', 'Electricity')->first();
        $data = array(
            'water_id' => $waterType->id ?? 0,
            'elect_id' => $electType->id ?? 0
        );

        return view('admin.account_type.create_account_type', ['data' => $data]);
    }
    public function editAccountTypeForm(Request $request, $id)
    {
        $account_type = AccountType::find($id);
        return view('admin.account_type.edit_account_type', ['account_type' => $account_type]);
    }
    public function createAccountType(Request $request)
    {
        $postData = $request->post();

        $account_type = AccountType::create(['type' => $postData['name']]);
        if ($account_type) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Account Type created successfully!');
            return redirect()->route('account-type-list');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }
    //End Account Type Code
    public function getEmailBasedRegion(Request $request, $id)
    {
        if (!empty($id)) {
            $regions = Regions::select('water_email','electricity_email')->where('id', $id)->first();
            return $regions;
        }
        return false;
    }
}
