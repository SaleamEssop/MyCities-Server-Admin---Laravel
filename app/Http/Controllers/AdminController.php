<?php

namespace App\Http\Controllers;

use App\Models\Account;
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
}
