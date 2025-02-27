<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use App\Models\Regions;
use App\Models\Property;
use App\Models\FixedCost;
use App\Models\MeterType;
use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Models\MeterCategory;
use App\Models\RegionsAccountTypeCost;
use Illuminate\Support\Facades\Session;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $properties = Property::with('property_manager', 'cost')->latest()->get();
        $properties->transform(function ($property) {
            $property->billing_day_with_suffix = $this->getDaySuffix($property->billing_day);
            return $property;
        });

        return view('admin.property.index', compact('properties'));
    }

    protected function getDaySuffix($day)
    {
        if (in_array($day % 10, [1, 2, 3]) && !in_array($day % 100, [11, 12, 13])) {
            return ['st', 'nd', 'rd'][$day % 10 - 1];
        }
        return 'th';
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $propertyManagers = User::where('is_property_manager', 1)->get();
        $RegionsAccountTypeCost = RegionsAccountTypeCost::all();

        return view('admin.property.create', compact('propertyManagers', 'RegionsAccountTypeCost'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    
        $request->validate([
            'name' => 'required',
            'contact_person' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'property_manager_id' => 'nullable|exists:users,id',
            'billing_day' => 'required|integer',
            'region_cost_id' => 'nullable|exists:regions_account_type_cost,id',
        ]);

        $property = new Property();
        $property->name = $request->name;
        $property->contact_person = $request->contact_person;
        $property->address = $request->address;
        $property->description = $request->description;
        $property->phone = $request->phone;
        $property->whatsapp = $request->whatsapp;
        $property->property_manager_id = $request->property_manager_id;
        $property->billing_day = $request->billing_day;
        $property->region_cost_id = $request->region_cost_id;
        $property->save();

        if ($property) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Property added successfully!');
            return redirect('admin/properties-list');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $property = Property::where('id', $id)->first();
        $propertyManager = User::where('id', $property->property_manager_id)->first();
        $propertyAccounts = Account::with('site', 'region','meters','accountType','user')->where('property_id', $property->id)->get();
        $propertyAccountsMeters = [];
        foreach ($propertyAccounts as $account) {
            $propertyAccountsMeters[] = $account->meters;
        }
        $propertyAccountsMeters = collect($propertyAccountsMeters)->flatten();
    
        $readings = []; 
        foreach ($propertyAccountsMeters as $meter) {
            $readings[] = $meter->readings;
        }
        $propertyAccountsMetersReadings = collect($readings)->flatten();


        return view('admin.property.show', compact('property', 'propertyManager', 'propertyAccounts', 'propertyAccountsMeters', 'propertyAccountsMetersReadings'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $property = Property::where('id', $id)->first();
        $propertyManagers = User::where('is_property_manager', 1)->get();
        $RegionsAccountTypeCost = RegionsAccountTypeCost::all();
        return view('admin.property.edit', compact('property', 'propertyManagers', 'RegionsAccountTypeCost'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //validate and update
        $validatedData = $request->validate([
            'name' => 'required',
            'contact_person' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'phone' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'property_manager_id' => 'nullable|exists:users,id',
            'billing_day' => 'required|integer',
            'region_cost_id' => 'nullable|exists:regions_account_type_cost,id',
        ]);
        $property = Property::where('id', $id)->first();
        $property->name = $request->name;
        $property->contact_person = $request->contact_person;
        $property->address = $request->address;
        $property->description = $request->description;
        $property->phone = $request->phone;
        $property->whatsapp = $request->whatsapp;
        $property->property_manager_id = $request->property_manager_id;
        $property->billing_day = $request->billing_day;
        $property->region_cost_id = $request->region_cost_id;
        $property->save();
        if ($property) {
            Session::flash('alert-class', 'alert-success');
            Session::flash('alert-message', 'Property updated successfully!');
            return redirect('admin/properties-list');
        } else {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('alert-message', 'Oops, something went wrong.');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function destroy(Property $property)
    {
        $property->delete();
    }


    public function addAccountForm($id)
    {

        $property = Property::where('id', $id)->first();
        $properties = Property::all();
        $regions = Regions::all();
        $accountTypes = AccountType::all();
        $users = User::where(['is_admin' => 0, 'is_super_admin' => 0])->get();
        $defaultCosts = FixedCost::where('is_default', 1)->get();
        return view('admin.create_account', compact('property', 'users', 'defaultCosts', 'properties', 'accountTypes', 'regions'));
    }


    //use region id and get electricity_email and get water_email
    public function getRegionEmails($id)
    {
        $region = Regions::where('id', $id)->first();
        return response()->json($region);
    }


    //addMeterForm
    public function addMeterForm($id)
    {
        $property = Property::where('id', $id)->first();
        $accounts = Account::where('property_id', $property->id)->get();
        $meterTypes = MeterType::all();
        $meterCats = MeterCategory::all();
        return view('admin.create_meter', ['accounts' => $accounts, 'meterTypes' => $meterTypes, 'meterCats' => $meterCats]);
    }
}
