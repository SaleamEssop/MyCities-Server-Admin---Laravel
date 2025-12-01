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

    // --- VIEW RENDERERS (LISTS) ---

    public function showUsers(Request $request)
    {
        $adminID = Auth::user()->id;
        $users = User::whereNotIn('id', [$adminID])->get();
        return view('admin.users', ['users' => $users]);
    }

    public function showAccounts(Request $request)
    {
        $accounts = Account::with('site')->get();
        return view('admin.accounts', ['accounts' => $accounts]);
    }

    public function showSites(Request $request)
    {
        $sites = Site::with(['user', 'region'])->get();
        return view('admin.sites', ['sites' => $sites]);
    }

    public function showRegions(Request $request)
    {
        $regions = Regions::all();
        return view('admin.regions', ['regions' => $regions]);
    }

    public function showAccountType(Request $request)
    {
        $account_type = AccountType::all();
        return view('admin.account_type.account_type', ['account_type' => $account_type]);
    }

    public function showMeters(Request $request)
    {
        $meters = Meter::with(['account', 'meterTypes'])->get();
        return view('admin.meters', ['meters' => $meters]);
    }

    public function showReadings(Request $request)
    {
        $readings = MeterReadings::with('meter')->orderBy('id', 'desc')->get();
        return view('admin.meter_readings', ['readings' => $readings]);
    }

    public function showAlarms(Request $request)
    {
        return view('admin.alarms', ['alarms' => []]); 
    }


    // --- VIEW RENDERERS (ADD FORMS) ---

    public function addUserForm(Request $request)
    {
        return view('admin.create_user');
    }

    public function addSiteForm(Request $request)
    {
        $users = User::all();
        $regions = Regions::all();
        return view('admin.create_site', ['users' => $users, 'regions' => $regions]);
    }

    public function addAccountForm(Request $request)
    {
        $users = User::all();
        $sites = Site::all(); 
        // Only fetch costs marked as default to avoid crashing or showing irrelevant costs
        $defaultCosts = FixedCost::where('is_default', 1)->get(); 

        return view('admin.create_account', [
            'users' => $users, 
            'sites' => $sites,
            'defaultCosts' => $defaultCosts
        ]);
    }

    public function addAccountTypeForm(Request $request)
    {
        return view('admin.account_type.create_account_type');
    }

    public function addMeterForm(Request $request)
    {
        $accounts = Account::all();
        $meterTypes = MeterType::all();
        $meterCats = MeterCategory::all();
        return view('admin.create_meter', [
            'accounts' => $accounts,
            'meterTypes' => $meterTypes,
            'meterCats' => $meterCats
        ]);
    }

    public function addReadingForm(Request $request)
    {
        $meters = Meter::with('account')->get();
        return view('admin.create_meter_reading', ['meters' => $meters]);
    }
    
    public function addRegionForm(Request $request)
    {
        $waterType = MeterType::where('title', 'water')->first();
        $electType = MeterType::where('title', 'electricity')->first();

        $data['water_id'] = $waterType ? $waterType->id : 0;
        $data['elect_id'] = $electType ? $electType->id : 0;

        return view('admin.create_region', ['data' => $data]);
    }


    // --- CRUD ACTIONS (CREATE / EDIT / DELETE) ---

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
            'password' => bcrypt($postData['password']),
            'is_admin' => 0
        );

        $result = User::create($userArr);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'User created successfully!');
            return redirect(route('show-users'));
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function deleteUser($id)
    {
        $adminID = Auth::user()->id;
        if ($id == $adminID) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, you cannot delete yourself!');
            return redirect()->back();
        }

        $deleted = User::where('id', $id)->first()->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'User deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back();
        }
    }

    public function editUserForm($id)
    {
        $user = User::where('id', $id)->first();
        return view('admin.edit_user', ['user' => $user]);
    }

    public function editUser(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back();
        }

        // Update user
        $updArr = array(
            'name' => $postData['name'],
            'email' => $postData['email'],
            'contact_number' => $postData['contact_number']
        );

        if (!empty($postData['password'])) {
            $updArr['password'] = bcrypt($postData['password']);
        }

        $updated = User::where('id', $postData['id'])->update($updArr);
        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'User updated successfully!');
            return redirect(route('show-users'));
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back();
        }
    }

    // --- SITES ---

    public function createSite(Request $request)
    {
        $postData = $request->post();
        
        $site = Site::create([
            'user_id' => $postData['user_id'],
            'title' => $postData['title'],
            'lat' => $postData['lat'],
            'lng' => $postData['lng'],
            'address' => $postData['address'],
            'email' => $postData['email'] ?? null,
            'region_id' => $postData['region_id'],
            'billing_type' => $postData['billing_type'] ?? 'monthly',
            'site_username' => $postData['site_username'] ?? null
        ]);

        if ($site) {
            if (!empty($postData['site_password'])) {
                $site->site_password = bcrypt($postData['site_password']);
                $site->save();
            }
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Site created successfully!');
            return redirect(route('show-sites'));
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function editSiteForm($id)
    {
        $site = Site::where('id', $id)->with('user')->first();
        $users = User::all();
        $regions = Regions::all();
        return view('admin.edit_site', ['site' => $site, 'users' => $users, 'regions' => $regions]);
    }

    public function editSite(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back();
        }

        $site = Site::find($postData['id']);
        if (!$site) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, site not found.');
            return redirect()->back();
        }

        $site->user_id = $postData['user_id'];
        $site->title = $postData['title'];
        $site->lat = $postData['lat'];
        $site->lng = $postData['lng'];
        $site->address = $postData['address'];
        $site->email = $postData['email'];
        $site->region_id = $postData['region_id'];
        $site->billing_type = $postData['billing_type'] ?? 'monthly';
        $site->site_username = $postData['site_username'] ?? null;

        if (!empty($postData['site_password'])) {
            $site->site_password = bcrypt($postData['site_password']);
        }

        $updated = $site->save();

        if ($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Site updated successfully!');
            return redirect(route('show-sites'));
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back();
        }
    }

    public function deleteSite($id)
    {
        $deleted = Site::where('id', $id)->first()->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Site deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back();
        }
    }

    public function getSitesByUser(Request $request)
    {
        $postData = $request->post();
        if (empty($postData['user_id'])) {
            echo json_encode(['status' => 400, 'msg' => 'User_id is required!']);
            return;
        }
        $sites = Site::where('user_id', $postData['user_id'])->get();
        echo json_encode(['status' => 200, 'data' => $sites]);
    }

    // --- ACCOUNTS ---

    public function createAccount(Request $request)
    {
        $postData = $request->post();
        
        $result = Account::create([
            'site_id' => $postData['site_id'],
            'account_name' => $postData['title'],
            'account_number' => $postData['number'],
            'billing_date' => $postData['billing_date'],
            'optional_information' => $postData['optional_info']
        ]);

        if ($result) {
            // Handle Default Costs
            if (isset($postData['default_ids']) && count($postData['default_ids']) > 0) {
                $accountDefaultCosts = [];
                for ($i = 0; $i < count($postData['default_ids']); $i++) {
                    $accountDefaultCosts[$i]['account_id'] = $result->id;
                    $accountDefaultCosts[$i]['fixed_cost_id'] = $postData['default_ids'][$i];
                    $accountDefaultCosts[$i]['value'] = $postData['default_cost_value'][$i];
                    $accountDefaultCosts[$i]['created_at'] = date('Y-m-d H:i:s');
                    $accountDefaultCosts[$i]['updated_at'] = date('Y-m-d H:i:s');
                }
                AccountFixedCost::insert($accountDefaultCosts);
            }

            // Handle Additional Costs
            if (isset($postData['additional_cost_name']) && count($postData['additional_cost_name']) > 0) {
                $fixedCostArr = [];
                for ($i = 0; $i < count($postData['additional_cost_name']); $i++) {
                    $fixedCostArr[$i]['account_id'] = $result->id;
                    $fixedCostArr[$i]['title'] = $postData['additional_cost_name'][$i];
                    $fixedCostArr[$i]['value'] = $postData['additional_cost_value'][$i];
                    $fixedCostArr[$i]['created_at'] = date('Y-m-d H:i:s');
                    $fixedCostArr[$i]['updated_at'] = date('Y-m-d H:i:s');
                }
                FixedCost::insert($fixedCostArr);
            }

            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Account created successfully!');
            return redirect(route('account-list'));
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function deleteAccount($id)
    {
        $deleted = Account::where('id', $id)->first()->delete();
        if ($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Account deleted successfully!');
            return redirect()->back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong!');
            return redirect()->back();
        }
    }

    public function editAccountForm($id)
    {
        $account = Account::where('id', $id)->with(['site', 'fixedCosts'])->first();
        $users = User::all();
        $sites = Site::all();
        return view('admin.edit_account', ['account' => $account, 'users' => $users, 'sites' => $sites]);
    }

    public function editAccount(Request $request)
    {
        $postData = $request->post();
        // Simple redirect for now, assuming edit logic will be implemented if needed
        return redirect(route('account-list'));
    }

    // --- ACCOUNT TYPE (TARIFFS) ---

    public function createAccountType(Request $request)
    {
        $postData = $request->post();
        $result = AccountType::create([
            'type' => $postData['name']
        ]);
        if ($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Tariff Category created successfully!');
            return redirect(route('account-type-list'));
        }
        return redirect()->back();
    }

    public function deleteAccountType($id)
    {
        AccountType::where('id', $id)->delete();
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function editAccountTypeForm($id)
    {
        $account_type = AccountType::find($id);
        return view('admin.account_type.edit_account_type', ['account_type' => $account_type]);
    }
    
    public function editAccountType(Request $request)
    {
        $postData = $request->post();
        AccountType::where('id', $postData['id'])->update(['type' => $postData['name']]);
        Session::flash('alert-class', 'alert-success');
        return redirect(route('account-type-list'));
    }

    // --- REGIONS ---

    public function createRegion(Request $request)
    {
        $postData = $request->post();
        
        $region = Regions::create([
            'name' => $postData['region_name'],
            'water_email' => $postData['water_email'] ?? '',
            'electricity_email' => $postData['electricity_email'] ?? ''
        ]);

        if ($region) {
            // Check for cost builders logic here...
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Region created successfully!');
            return redirect(route('regions-list'));
        }
        
        return redirect()->back();
    }

    public function deleteRegion($id)
    {
        Regions::where('id', $id)->delete();
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function editRegionForm($id)
    {
        $region = Regions::find($id);
        $waterType = MeterType::where('title', 'water')->first();
        $electType = MeterType::where('title', 'electricity')->first();

        $data['water_id'] = $waterType ? $waterType->id : 0;
        $data['elect_id'] = $electType ? $electType->id : 0;
        
        return view('admin.edit_region', ['region' => $region, 'data' => $data]);
    }

    public function editRegion(Request $request)
    {
         // Logic for edit region
         return redirect(route('regions-list'));
    }
    
    public function getEmailBasedRegion($id)
    {
        $region = Regions::where('id', $id)->first();
        echo json_encode(['status' => 200, 'data' => $region]);
    }

    // --- METERS ---
    
    public function createMeter(Request $request)
    {
        $postData = $request->post();
        Meter::create([
            'account_id' => $postData['account_id'],
            'meter_category_id' => $postData['meter_cat_id'],
            'meter_type_id' => $postData['meter_type_id'],
            'meter_title' => $postData['title'],
            'meter_number' => $postData['number']
        ]);
        Session::flash('alert-class', 'alert-success');
        return redirect(route('meters-list'));
    }
}
