<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountFixedCost;
use App\Models\AccountType;
use App\Models\FixedCost;
use App\Models\Meter;
use App\Models\MeterReadings;
use App\Models\MeterType;
use App\Models\MeterCategory;
use App\Models\Regions;
use App\Models\Site;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['email']) || empty($postData['password']))
            return redirect()->back();

        $user = User::where('email', $postData['email'])->first();
        if (empty($user) || !Hash::check($postData['password'], $user->password)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, wrong credentials provided!');
            return redirect()->back();
        }
        if (!$user->is_admin) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, you are not authorized to access admin panel!');
            return redirect()->back();
        }
        Auth::login($user);
        return redirect()->intended('admin/');
    }

    public function showUsers() { return view('admin.users', ['users' => User::where('id', '!=', Auth::id())->get()]); }
    public function showAccounts() { return view('admin.accounts', ['accounts' => Account::with('site')->get()]); }
    public function showSites() { return view('admin.sites', ['sites' => Site::with(['user', 'region'])->get()]); }
    public function showRegions() { return view('admin.regions', ['regions' => Regions::all()]); }
    public function showAccountType() { return view('admin.account_type.account_type', ['account_type' => AccountType::all()]); }
    public function showMeters() { return view('admin.meters', ['meters' => Meter::with(['account', 'meterTypes'])->get()]); }
    public function showReadings() { return view('admin.meter_readings', ['readings' => MeterReadings::with('meter')->orderBy('id', 'desc')->get()]); }
    public function showAlarms() { return view('admin.alarms', ['alarms' => []]); }
    
    public function showPayments() { 
        return view('admin.payments', ['payments' => Payment::with('account')->orderBy('payment_date', 'desc')->get()]); 
    }

    public function addUserForm() { return view('admin.create_user'); }
    public function addAccountTypeForm() { return view('admin.account_type.create_account_type'); }
    
    public function addSiteForm() { 
        return view('admin.create_site', ['users' => User::all(), 'regions' => Regions::all()]); 
    }

    public function addAccountForm() { 
        try {
            $defaultCosts = FixedCost::where('is_default', 1)->get(); 
        } catch (\Exception $e) { $defaultCosts = []; }
        return view('admin.create_account', ['users' => User::all(), 'sites' => Site::all(), 'defaultCosts' => $defaultCosts]);
    }

    public function addMeterForm() {
        return view('admin.create_meter', ['accounts' => Account::all(), 'meterTypes' => MeterType::all(), 'meterCats' => MeterCategory::all()]);
    }

    public function addReadingForm() {
        return view('admin.create_meter_reading', ['meters' => Meter::with('account')->get()]);
    }
    
    public function addRegionForm() {
        $w = MeterType::where('title', 'water')->first();
        $e = MeterType::where('title', 'electricity')->first();
        return view('admin.create_region', ['data' => ['water_id' => $w->id??0, 'elect_id' => $e->id??0]]);
    }

    public function addPaymentForm() {
        $sites = Site::all();
        return view('admin.create_payment', ['sites' => $sites]);
    }

    public function createUser(Request $request) {
        $postData = $request->post();
        if(User::where('email', $postData['email'])->exists()) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Email exists');
            return redirect()->back();
        }
        User::create(['name'=>$postData['name'], 'email'=>$postData['email'], 'contact_number'=>$postData['contact_number'], 'password'=>bcrypt($postData['password']), 'is_admin'=>0]);
        return redirect(route('show-users'));
    }

    public function deleteUser($id) { User::destroy($id); return redirect()->back(); }
    public function editUserForm($id) { return view('admin.edit_user', ['user'=>User::find($id)]); }
    public function editUser(Request $request) { User::where('id', $request->id)->update(['name'=>$request->name, 'email'=>$request->email]); return redirect(route('show-users')); }

    public function createSite(Request $request) {
        $postData = $request->post();
        Site::create([
            'user_id' => $postData['user_id'], 
            'title' => $postData['title'], 
            'lat' => $postData['lat'] ?? 0.0,     // Default to 0 if not provided
            'lng' => $postData['lng'] ?? 0.0,     // Default to 0 if not provided
            'address' => $postData['address'], 
            'email' => $postData['email'] ?? null, 
            'region_id' => $postData['region_id'], 
            'billing_type' => $postData['billing_type'] ?? 'monthly', 
            'site_username' => $postData['site_username'] ?? null
        ]);
        return redirect(route('show-sites'));
    }

    public function deleteSite($id) { Site::destroy($id); return redirect()->back(); }
    public function editSiteForm($id) { return view('admin.edit_site', ['site'=>Site::find($id), 'users'=>User::all(), 'regions'=>Regions::all()]); }
    public function editSite(Request $request) { Site::where('id', $request->id)->update(['title'=>$request->title]); return redirect(route('show-sites')); }

    public function createAccount(Request $request) {
        $postData = $request->post();
        $acc = Account::create([
            'site_id'=>$postData['site_id'], 'account_name'=>$postData['title'], 'account_number'=>$postData['number'], 
            'billing_date'=>$postData['billing_date'], 'optional_information'=>$postData['optional_info']
        ]);
        if(isset($postData['default_ids'])) {
            foreach($postData['default_ids'] as $k => $v) {
                AccountFixedCost::create(['account_id'=>$acc->id, 'fixed_cost_id'=>$v, 'value'=>$postData['default_cost_value'][$k]]);
            }
        }
        return redirect(route('account-list'));
    }
    public function deleteAccount($id) { Account::destroy($id); return redirect()->back(); }
    public function editAccountForm($id) { return view('admin.edit_account', ['account'=>Account::find($id), 'users'=>User::all(), 'sites'=>Site::all()]); }
    
    public function editAccount(Request $request) {
        $postData = $request->post();
        $acc = Account::find($request->id);
        
        if($acc) {
            // 1. Update the main account details
            $acc->update([
                'site_id' => $postData['site_id'], 
                'account_name' => $postData['title'], 
                'account_number' => $postData['number'], 
                'billing_date' => $postData['billing_date'], 
                'optional_information' => $postData['optional_info']
            ]);

            // 2. Update the costs associated with the account
            if(isset($postData['default_ids'])) {
                // Remove old entries to prevent duplicates
                AccountFixedCost::where('account_id', $acc->id)->delete();
                
                // Add the new values
                foreach($postData['default_ids'] as $k => $v) {
                    AccountFixedCost::create([
                        'account_id' => $acc->id, 
                        'fixed_cost_id' => $v, 
                        'value' => $postData['default_cost_value'][$k]
                    ]);
                }
            }

            // 3. Set the success message
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Account saved successfully!');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Account not found.');
        }
        
        return redirect(route('account-list'));
    }
    
    public function getAccountsBySite(Request $request) {
        $accounts = Account::where('site_id', $request->site_id)->get();
        return response()->json(['status' => 200, 'data' => $accounts]);
    }

    public function getAccountDetails(Request $request) {
        $account = Account::with(['site', 'meters', 'payments' => function($q) {
            $q->latest()->take(3);
        }])->find($request->account_id);

        if (!$account) {
            return response()->json(['status' => 404, 'message' => 'Account not found']);
        }

        $data = [
            'account_name' => $account->account_name,
            'account_number' => $account->account_number,
            'site_name' => $account->site->title ?? 'Unknown Site',
            'meters' => $account->meters->pluck('meter_number')->toArray(),
            'recent_payments' => $account->payments,
        ];

        return response()->json(['status' => 200, 'data' => $data]);
    }

    public function createMeter(Request $request) {
        $postData = $request->post();
        Meter::create([
            'account_id'=>$postData['account_id'], 'meter_category_id'=>$postData['meter_cat_id'], 
            'meter_type_id'=>$postData['meter_type_id'], 'meter_title'=>$postData['title'], 'meter_number'=>$postData['number']
        ]);
        return redirect(route('meters-list'));
    }

    public function createReading(Request $request)
    {
        $postData = $request->post();
        $readingImg = null;
        if ($request->hasFile('reading_image'))
            $readingImg = $request->file('reading_image')->store('public/readings');

        $res = MeterReadings::create([
            'meter_id' => $postData['meter_id'],
            'reading_date' => $postData['reading_date'],
            'reading_value' => $postData['reading_value'],
            'reading_image' => $readingImg
        ]);

        if ($res) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Reading added successfully!');
            return redirect(route('meter-reading-list'));
        }
        Session::flash('alert-class', 'alert-danger');
        return redirect()->back();
    }

    public function createPayment(Request $request)
    {
        $postData = $request->post();
        $res = Payment::create([
            'account_id' => $postData['account_id'],
            'amount' => $postData['amount'],
            'payment_date' => $postData['payment_date'],
            'reference' => $postData['reference'] ?? '',
            'notes' => $postData['notes'] ?? ''
        ]);

        if ($res) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Payment added successfully!');
            return redirect(route('payments-list'));
        }
        return redirect()->back();
    }
    
    public function deletePayment($id) { Payment::destroy($id); return redirect()->back(); }
    
    public function getSitesByUser(Request $request) {
        echo json_encode(['status'=>200, 'data'=>Site::where('user_id', $request->user_id)->get()]);
    }
    
    public function createAccountType(Request $request) {
         AccountType::create(['type' => $request->name]);
         return redirect(route('account-type-list'));
    }
    public function deleteAccountType($id) { AccountType::destroy($id); return redirect()->back(); }
    public function editAccountTypeForm($id) { return view('admin.account_type.edit_account_type', ['account_type' => AccountType::find($id)]); }
    public function editAccountType(Request $request) { AccountType::where('id', $request->id)->update(['type'=>$request->name]); return redirect(route('account-type-list')); }

    public function createRegion(Request $request) {
        Regions::create(['name' => $request->region_name, 'water_email' => $request->water_email??'', 'electricity_email' => $request->electricity_email??'']);
        return redirect(route('regions-list'));
    }
    public function deleteRegion($id) { Regions::destroy($id); return redirect()->back(); }
    public function editRegionForm($id) { 
        $d['water_id'] = MeterType::where('title','water')->first()->id??0; $d['elect_id'] = MeterType::where('title','electricity')->first()->id??0;
        return view('admin.edit_region', ['region' => Regions::find($id), 'data'=>$d]); 
    }
    public function editRegion(Request $request) { return redirect(route('regions-list')); }
    public function getEmailBasedRegion($id) { echo json_encode(['status'=>200, 'data'=>Regions::find($id)]); }
}
