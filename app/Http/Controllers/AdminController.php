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

}
