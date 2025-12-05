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
use Illuminate\Support\Facades\Schema;

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
     * Validate email uniqueness via AJAX
     */
    public function validateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        
        $exists = User::where('email', $request->email)->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }

    /**
     * Validate phone number uniqueness via AJAX
     */
    public function validatePhone(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|string'
        ]);
        
        $exists = User::where('contact_number', $request->contact_number)->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }

    /**
     * Store only user details (without region, tariff, account, meters)
     */
    public function storeUserOnly(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|string|max:20|unique:users,contact_number',
            'password' => 'required|string|min:6',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'password' => Hash::make($request->password),
                'is_admin' => 0,
            ]);
            
            return response()->json([
                'status' => 200, 
                'message' => 'User created successfully. You can add region, account, and meters later from the mobile app.',
                'user_id' => $user->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500, 
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new user with all related data through the wizard
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|string|max:20|unique:users,contact_number',
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
            
            // Step 4: Create account - check if tariff_template_id column exists
            $accountData = [
                'site_id' => $site->id,
                'account_name' => $request->account_name ?? ($request->name . "'s Account"),
                'account_number' => $request->account_number ?? ('ACC-' . time()),
                'billing_date' => $request->billing_date ?? null,
                'bill_day' => $request->bill_day ?? null,
                'read_day' => $request->read_day ?? null,
            ];
            
            // Only include tariff_template_id if the column exists
            if (Schema::hasColumn('accounts', 'tariff_template_id')) {
                $accountData['tariff_template_id'] = $request->tariff_template_id;
            }
            
            $account = Account::create($accountData);
            
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
                    
                    // Add initial reading if provided - use read_day from account if no specific date
                    if (!empty($meterData['initial_reading'])) {
                        $readingDate = now()->format('Y-m-d');
                        // If read_day is set in account, use it to calculate reading date
                        if ($request->read_day) {
                            $readingDate = now()->setDay(min($request->read_day, now()->daysInMonth))->format('Y-m-d');
                        }
                        
                        MeterReadings::create([
                            'meter_id' => $meter->id,
                            'reading_date' => $readingDate,
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
