<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\Account;
use App\Models\Regions;
use App\Models\Property;
use App\Models\FixedCost;
use App\Models\MeterType;
use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Models\FixedCostProp;
use App\Models\MeterCategory;
use App\Models\PropertyFixedCost;
use Illuminate\Support\Facades\Auth;
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

        $regions = Regions::all();
        $propertyManagers = User::where('is_property_manager', 1)->get();
        $RegionsAccountTypeCost = RegionsAccountTypeCost::all();
        $defaultCosts = FixedCost::where('is_default', 1)->get();


        return view('admin.property.create', compact('propertyManagers', 'RegionsAccountTypeCost','defaultCosts', 'regions'));
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
            'site' => 'required|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'fetched-email' => 'nullable|string',
            'region_id' => 'nullable|exists:regions,id',
            'electricity_email' => 'nullable|string',
            'water_email' => 'nullable|string',
        ]);


        $postData = $request->post();
        $siteArr = [
            'user_id' => $request->property_manager_id,
            'title' => $postData['fetched-email'] ?? '',
            'lat' => $postData['lat'] ?? "",
            'lng' => $postData['lng'] ?? "",
            'address' => $postData['site'],
            'email' => $postData['fetched-email'] ?? '',
        ];

        $siteExists = Site::where('user_id', $request->property_manager_id)
            ->where('address', $postData['site'])
            ->first();

        if ($siteExists) {
            $siteID = $siteExists->id;
        } else {
            $site = Site::create($siteArr);
            $siteID = $site->id;
        }

        $property = new Property();
        $property->site_id = $siteID;
        $property->region_id = $request->region_id;
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

            // Add default cost
            if (!empty($postData['default_cost_value']) && $postData['default_ids']) {
                $accountDefaultCosts = [];
                for ($i = 0; $i < count($postData['default_ids']); $i++) {
                    $accountDefaultCosts[$i]['property_id'] = $property->id;
                    $accountDefaultCosts[$i]['fixed_cost_id'] = $postData['default_ids'][$i];
                    $accountDefaultCosts[$i]['value'] = $postData['default_cost_value'][$i];
                    $accountDefaultCosts[$i]['created_at'] = date('Y-m-d H:i:s');
                }
                if (!empty($accountDefaultCosts))
                    PropertyFixedCost::insert($accountDefaultCosts);
            }

            if (!empty($postData['additional_cost_name'])) {
                $fixedCostArr = [];
                for ($i = 0; $i < count($postData['additional_cost_name']); $i++) {
                    $fixedCostArr[$i]['property_id'] = $property->id;
                    $fixedCostArr[$i]['title'] = $postData['additional_cost_name'][$i];
                    $fixedCostArr[$i]['value'] = $postData['additional_cost_value'][$i];
                    $fixedCostArr[$i]['added_by'] = Auth::user()->id;
                    $fixedCostArr[$i]['created_at'] = date("Y-m-d H:i:s");
                }
                FixedCostProp::insert($fixedCostArr);
            }

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

        $accountUsers = [];
        foreach ($propertyAccounts as $account) {
            $accountUsers[] = $account->user;
        }

        

        return view('admin.property.show', compact('property', 'accountUsers' ,'propertyManager', 'propertyAccounts', 'propertyAccountsMeters', 'propertyAccountsMetersReadings'));
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
