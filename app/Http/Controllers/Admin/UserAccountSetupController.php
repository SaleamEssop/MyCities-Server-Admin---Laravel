<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Meter;
use App\Models\MeterReadings;
use App\Models\MeterType;
use App\Models\Regions;
use App\Models\RegionsAccountTypeCost;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAccountSetupController extends Controller
{
    // Default meter category ID
    private const DEFAULT_METER_CATEGORY_ID = 1;

    /**
     * Display the setup wizard page
     */
    public function index()
    {
        $regions = Regions::all();
        $meterTypes = MeterType::all();
        
        return view('admin.user-accounts.setup', [
            'regions' => $regions,
            'meterTypes' => $meterTypes,
        ]);
    }

    /**
     * Get tariff templates filtered by region
     */
    public function getTariffTemplatesByRegion($regionId)
    {
        $templates = RegionsAccountTypeCost::where('region_id', $regionId)
            ->where('is_active', 1)
            ->get(['id', 'template_name', 'is_water', 'is_electricity', 'start_date', 'end_date']);
        
        return response()->json([
            'status' => 200,
            'data' => $templates
        ]);
    }

    /**
     * Store a new user with all related data through the wizard
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'region_id' => 'required|exists:regions,id',
            'tariff_template_id' => 'required|exists:regions_account_type_cost,id',
        ]);

        DB::beginTransaction();
        
        try {
            // Step 1: Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'password' => Hash::make($request->password),
                'is_admin' => 0,
            ]);
            
            // Step 2 & 3: Create site with region
            $site = Site::create([
                'user_id' => $user->id,
                'title' => $request->site_title ?? ($request->name . "'s Site"),
                'lat' => $request->lat ?? 0,
                'lng' => $request->lng ?? 0,
                'address' => $request->address ?? '',
                'email' => $request->email,
                'region_id' => $request->region_id,
                'billing_type' => $request->billing_type ?? 'monthly',
                'site_username' => $request->site_username ?? null,
            ]);
            
            // Step 4: Create account with tariff template
            $account = Account::create([
                'site_id' => $site->id,
                'account_name' => $request->account_name ?? ($request->name . "'s Account"),
                'account_number' => $request->account_number ?? ('ACC-' . time()),
                'billing_date' => $request->billing_date ?? null,
                'tariff_template_id' => $request->tariff_template_id,
                'bill_day' => $request->bill_day ?? null,
                'read_day' => $request->read_day ?? null,
            ]);
            
            // Create meters if provided
            if ($request->has('meters') && is_array($request->meters)) {
                foreach ($request->meters as $meterData) {
                    $meter = Meter::create([
                        'account_id' => $account->id,
                        'meter_type_id' => $meterData['meter_type_id'] ?? null,
                        'meter_category_id' => $meterData['meter_category_id'] ?? self::DEFAULT_METER_CATEGORY_ID,
                        'meter_title' => $meterData['meter_title'] ?? '',
                        'meter_number' => $meterData['meter_number'] ?? '',
                    ]);
                    
                    // Add initial reading if provided
                    if (!empty($meterData['initial_reading'])) {
                        MeterReadings::create([
                            'meter_id' => $meter->id,
                            'reading_date' => $meterData['initial_reading_date'] ?? now()->format('Y-m-d'),
                            'reading_value' => $meterData['initial_reading'],
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200, 
                'message' => 'User account created successfully',
                'user_id' => $user->id,
                'account_id' => $account->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500, 
                'message' => 'Error creating user account: ' . $e->getMessage()
            ], 500);
        }
    }
}
