<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Meter;
use App\Models\MeterType;
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
        if(empty($postData['email']) || empty($postData['password']))
            return redirect()->back();

        // Verify credentials
        $user = User::where('email', $postData['email'])->first();
        if(empty($user)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, wrong email provided!');
            return redirect()->back();
        }

        // Now check the password hash
        $dbPasswordHash = $user->password;
        $userPassword = $postData['password'];
        if(!Hash::check($userPassword, $dbPasswordHash)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, wrong credentials provided!');
            return redirect()->back();
        }

        // Now check if incoming user is admin or not
        if(!$user->is_admin) {
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
        if(Auth::user()->is_super_admin)
            $users = User::whereNotIn('id', [$adminID])->get();
        else
            $users = []; // Because this admin should not have any users under it; this could be changed in the future

        return view('admin.users', ['users' => $users]);
    }

    public function showAccounts(Request $request)
    {
        if(Auth::user()->is_super_admin)
            $accounts = Account::with('site')->get();
        else
            $accounts = []; // Because this admin should not have any users under it; this could be changed in the future

        return view('admin.accounts', ['accounts' => $accounts]);
    }

    public function showSites(Request $request)
    {
        if(Auth::user()->is_super_admin)
            $sites = Site::with('user')->get();
        else
            $sites = []; // Because this admin should not have any users under it; this could be changed in the future

        return view('admin.sites', ['sites' => $sites]);
    }

    public function addUserForm(Request $request)
    {
        return view('admin.create_user');
    }

    public function createUser(Request $request)
    {
        $postData = $request->post();
        $userArr = array(
            'name' => $postData['name'],
            'email' => $postData['email'],
            'contact_number' => $postData['contact_number'],
            'password' => bcrypt($postData['password'])
        );
        $result = User::create($userArr);
        if($result) {
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
        if(empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        $deleted = User::where('id', $id)->delete();
        if($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! User deleted successfully!');
            return redirect()->back();
        }
        else {
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
        if(empty($postData['user_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, user ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'name' => $postData['name'],
            'contact_number' => $postData['contact_number'],
            'email' => $postData['email']
        );
        if(!empty($postData['password']))
            $updArr['password'] = bcrypt($postData['password']);

        $updated = User::where('id', $postData['user_id'])->update($updArr);
        if($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! User updated successfully!');
            return redirect('admin/users');
        }
        else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function addSiteForm(Request $request)
    {
        $adminID = Auth::user()->id;
        $users = User::whereNotIn('id', [$adminID])->get();
        return view('admin.create_site', ['users' => $users]);
    }

    public function createSite(Request $request)
    {
        $postData = $request->post();
        $siteArr = array(
            'user_id' => $postData['user_id'],
            'title' => $postData['title'],
            'lat' => $postData['lat'],
            'lng' => $postData['lng'],
            'email' => $postData['email'],
            'address' => $postData['address']
        );
        $result = Site::create($siteArr);
        if($result) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Site created successfully!');
            return redirect('admin/sites');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function deleteSite(Request $request, $id)
    {
        if(empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        $deleted = Site::where('id', $id)->delete();
        if($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Site deleted successfully!');
            return redirect()->back();
        }
        else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function addAccountForm(Request $request)
    {
        $sites = Site::all();
        return view('admin.create_account', ['sites' => $sites]);
    }

    public function createAccount(Request $request)
    {
        $postData = $request->post();
        $accArr = array(
            'site_id' => $postData['site_id'],
            'account_name' => $postData['title'],
            'account_number' => $postData['number'],
            'optional_information' => $postData['optional_info']
        );
        $result = Account::create($accArr);
        if($result) {
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
        if(empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        $deleted = Account::where('id', $id)->delete();
        if($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Account deleted successfully!');
            return redirect()->back();
        }
        else {
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
        return view('admin.edit_site', ['site' => $site, 'users' => $users]);
    }

    public function editSite(Request $request)
    {
        $postData = $request->post();
        if(empty($postData['site_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, site ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'user_id' => $postData['user_id'],
            'title' => $postData['title'],
            'lat' => $postData['lat'],
            'lng' => $postData['lng'],
            'address' => $postData['address'],
            'email' => $postData['email']
        );

        $updated = Site::where('id', $postData['site_id'])->update($updArr);
        if($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Site updated successfully!');
            return redirect('admin/sites');
        }
        else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function editAccountForm(Request $request, $id)
    {
        $sites = Site::all();
        $account = Account::find($id);
        return view('admin.edit_account', ['account' => $account, 'sites' => $sites]);
    }

    public function editAccount(Request $request)
    {
        $postData = $request->post();
        if(empty($postData['account_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, account ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'site_id' => $postData['site_id'],
            'account_name' => $postData['title'],
            'account_number' => $postData['number'],
            'optional_information' => $postData['optional_info']
        );

        $updated = Account::where('id', $postData['account_id'])->update($updArr);
        if($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Account    updated successfully!');
            return redirect('admin/accounts');
        }
        else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function showMeters(Request $request)
    {
        $meters = Meter::with(['meterTypes', 'account'])->get();
        return view('admin.meters', ['meters' => $meters]);
    }

    public function deleteMeter(Request $request, $id)
    {
        if(empty($id)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
        // In next phase delete all the related models as well.
        $deleted = Meter::where('id', $id)->delete();
        if($deleted) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Meter deleted successfully!');
            return redirect()->back();
        }
        else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    public function addMeterForm(Request $request)
    {
        $accounts = Account::all();
        $meterTypes = MeterType::all();
        return view('admin.create_meter', ['accounts' => $accounts, 'meterTypes' => $meterTypes]);
    }

    public function createMeter(Request $request)
    {
        $postData = $request->post();
        $meterArr = array(
            'account_id' => $postData['account_id'],
            'meter_type_id' => $postData['meter_type_id'],
            'meter_title' => $postData['title'],
            'meter_number' => $postData['number']
        );
        $result = Meter::create($meterArr);
        if($result) {
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
        $meterTypes = MeterType::all();
        $meter = Meter::find($id);
        return view('admin.edit_meter', ['accounts' => $accounts, 'meterTypes' => $meterTypes, 'meter' => $meter]);
    }

    public function editMeter(Request $request)
    {
        $postData = $request->post();
        if(empty($postData['meter_id'])) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, meter ID is required.');
            return redirect()->back();
        }

        $updArr = array(
            'account_id' => $postData['account_id'],
            'meter_type_id' => $postData['meter_type_id'],
            'meter_title' => $postData['title'],
            'meter_number' => $postData['number']
        );

        $updated = Meter::where('id', $postData['meter_id'])->update($updArr);
        if($updated) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Success! Meter updated successfully!');
            return redirect('admin/meters');
        }
        else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

}
