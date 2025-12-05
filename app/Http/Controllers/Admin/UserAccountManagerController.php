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

class UserAccountManagerController extends Controller
{
    // Meter type title constants - these match database values
    private const METER_TYPE_WATER = 'water';
    private const METER_TYPE_ELECTRICITY = 'electricity';
    
    // Default meter category ID
    private const DEFAULT_METER_CATEGORY_ID = 1;

    /**
     * Display the manager dashboard
     */
    public function index()
    {
        $users = User::withCount('sites')
            ->with(['sites.region'])
            ->get();
        $regions = Regions::all();
        $meterTypes = MeterType::all();
        
        return view('admin.user-accounts.manager', [
            'users' => $users,
            'regions' => $regions,
            'meterTypes' => $meterTypes,
        ]);
    }

    /**
     * Search users with filters
     */
    public function search(Request $request)
    {
        $query = User::withCount('sites');
        
        // Search by name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Search by address (through sites)
        if ($request->filled('address')) {
            $query->whereHas('sites', function($q) use ($request) {
                $q->where('address', 'like', '%' . $request->address . '%');
            });
        }
        
        // Search by phone
        if ($request->filled('phone')) {
            $query->where('contact_number', 'like', '%' . $request->phone . '%');
        }
        
        // Filter by user type
        if ($request->filled('user_type')) {
            if ($request->user_type === 'test') {
                $query->where('email', 'like', '%@test.com');
            } elseif ($request->user_type === 'real') {
                $query->where('email', 'not like', '%@test.com');
            }
        }
        
        $users = $query->get();
        
        return response()->json([
            'status' => 200,
            'data' => $users
        ]);
    }

    /**
     * Get user data with all related entities for editing
     */
    public function getUserData($id)
    {
        $user = User::with([
            'sites.accounts.meters.readings' => function($query) {
                $query->orderBy('reading_date', 'desc');
            },
            'sites.accounts.tariffTemplate',
            'sites.region'
        ])->find($id);
        
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'User not found']);
        }
        
        return response()->json(['status' => 200, 'data' => $user]);
    }

    /**
     * Update user basic details
     */
    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'contact_number' => 'required|string|max:20',
        ]);

        try {
            $user = User::findOrFail($id);
            
            $user->name = $request->name;
            $user->email = $request->email;
            $user->contact_number = $request->contact_number;
            
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            
            $user->save();
            
            return response()->json([
                'status' => 200, 
                'message' => 'User updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500, 
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update account details
     */
    public function updateAccount(Request $request, $id)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
        ]);

        try {
            $account = Account::findOrFail($id);
            
            $account->account_name = $request->account_name;
            $account->account_number = $request->account_number;
            $account->billing_date = $request->billing_date;
            $account->tariff_template_id = $request->tariff_template_id;
            $account->bill_day = $request->bill_day;
            $account->read_day = $request->read_day;
            
            $account->save();
            
            return response()->json([
                'status' => 200, 
                'message' => 'Account updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500, 
                'message' => 'Error updating account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a meter to an account
     */
    public function addMeter(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'meter_type_id' => 'required|exists:meter_types,id',
            'meter_title' => 'required|string|max:255',
            'meter_number' => 'required|string|max:50',
        ]);

        try {
            $meter = Meter::create([
                'account_id' => $request->account_id,
                'meter_type_id' => $request->meter_type_id,
                'meter_category_id' => $request->meter_category_id ?? self::DEFAULT_METER_CATEGORY_ID,
                'meter_title' => $request->meter_title,
                'meter_number' => $request->meter_number,
            ]);
            
            // Add initial reading if provided
            if ($request->filled('initial_reading')) {
                MeterReadings::create([
                    'meter_id' => $meter->id,
                    'reading_date' => $request->initial_reading_date ?? now()->format('Y-m-d'),
                    'reading_value' => $request->initial_reading,
                ]);
            }
            
            return response()->json([
                'status' => 200, 
                'message' => 'Meter added successfully',
                'meter_id' => $meter->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500, 
                'message' => 'Error adding meter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update meter details
     */
    public function updateMeter(Request $request, $id)
    {
        $request->validate([
            'meter_title' => 'required|string|max:255',
            'meter_number' => 'required|string|max:50',
        ]);

        try {
            $meter = Meter::findOrFail($id);
            
            $meter->meter_title = $request->meter_title;
            $meter->meter_number = $request->meter_number;
            $meter->meter_type_id = $request->meter_type_id;
            
            $meter->save();
            
            return response()->json([
                'status' => 200, 
                'message' => 'Meter updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500, 
                'message' => 'Error updating meter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a meter
     */
    public function deleteMeter($id)
    {
        try {
            $meter = Meter::findOrFail($id);
            $meter->delete();
            
            return response()->json([
                'status' => 200, 
                'message' => 'Meter deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500, 
                'message' => 'Error deleting meter: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a reading to a meter with validation
     */
    public function addReading(Request $request)
    {
        $request->validate([
            'meter_id' => 'required|exists:meters,id',
            'reading_date' => 'required|date',
            'reading_value' => 'required|string', // String to preserve leading zeros for water meters
        ]);

        try {
            $meter = Meter::findOrFail($request->meter_id);
            
            // Get the previous reading for validation
            $previousReading = MeterReadings::where('meter_id', $request->meter_id)
                ->orderBy('reading_date', 'desc')
                ->first();
            
            // Validate date is not earlier than previous reading
            if ($previousReading) {
                $newDate = strtotime($request->reading_date);
                $prevDate = strtotime($previousReading->reading_date);
                
                if ($newDate < $prevDate) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Reading date cannot be earlier than the previous reading date (' . $previousReading->reading_date . ')'
                    ], 400);
                }
                
                // Validate reading value is not lower than previous
                $newValue = floatval($request->reading_value);
                $prevValue = floatval($previousReading->reading_value);
                
                if ($newValue < $prevValue) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Reading value cannot be lower than the previous reading (' . $previousReading->reading_value . ')'
                    ], 400);
                }
            }
            
            $reading = MeterReadings::create([
                'meter_id' => $request->meter_id,
                'reading_date' => $request->reading_date,
                'reading_value' => $request->reading_value,
            ]);
            
            return response()->json([
                'status' => 200, 
                'message' => 'Reading added successfully',
                'reading_id' => $reading->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500, 
                'message' => 'Error adding reading: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get readings history for a meter
     */
    public function getReadings($meterId)
    {
        $readings = MeterReadings::where('meter_id', $meterId)
            ->orderBy('reading_date', 'desc')
            ->get();
        
        return response()->json([
            'status' => 200,
            'data' => $readings
        ]);
    }

    /**
     * Delete a user and all related data
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete(); // Cascade delete handled by model
            
            return response()->json([
                'status' => 200, 
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500, 
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tariff templates by region
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
}
