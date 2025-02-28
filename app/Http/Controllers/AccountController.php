<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\Account;
use App\Models\Regions;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{
    public function showDetail($id)
    {
        //get property of account
        
        $account = Account::with(['site', 'region', 'meters.readings', 'accountType'])
                                   ->where('id', $id)
                                   ->firstOrFail(); 
        $propertyAccountsMeters = $account->meters;
        $propertyAccountsMetersReadings = $propertyAccountsMeters->flatMap(function ($meter) {
            return $meter->readings;
        });
        $property = $account->property;
        $propertyManager = $property->property_manager;

        return view('admin.account.show', compact('property', 'propertyManager', 'account', 'propertyAccountsMeters', 'propertyAccountsMetersReadings'));
    }

    public function edit($id)
    {
        $account = Account::where('id', $id)->first();
        $users = User::all();
        $sites = Site::all();
        $regions = Regions::all();
        $accountTypes = AccountType::all();
        
        return view('admin.edit_account', compact('account', 'users', 'sites', 'regions', 'accountTypes'));
    }

    public function update(Request $request, $id)
    {
        //validate and update
        $validatedData = $request->validate([
            'account_name' => 'required',
            'account_number' => 'required',
            'billing_date' => 'required',
            'optional_information' => 'required',
            'region_id' => 'required',
            'account_type_id' => 'required',
            'water_email' => 'required',
            'electricity_email' => 'required',
            'bill_day' => 'required',
            'read_day' => 'required',
            'bill_read_day_active' => 'required',
        ]);

        $account = Account::where('id', $id)->first();
        $account->update($validatedData);
        if ($account) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Account updated successfully!');
            return back();
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }

        return redirect()->route('admin.accounts.show', $id)->with('success', 'Account updated successfully');
    }

    public function destroy($id)
    {
        $account = Account::where('id', $id)->first();
        $account->delete();

        return redirect()->route('admin.properties.index')->with('success', 'Account deleted successfully');
    }

}

