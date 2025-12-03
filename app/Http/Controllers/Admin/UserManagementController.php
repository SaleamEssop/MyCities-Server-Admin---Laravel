<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Meter;
use App\Models\MeterReadings;
use App\Models\MeterType;
use App\Models\Regions;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserManagementController extends Controller
{
    /**
     * Display the user management page
     */
    public function index()
    {
        $users = User::withCount('sites')->get();
        $regions = Regions::all();
        $accountTypes = AccountType::all();
        $meterTypes = MeterType::all();
        
        return view('admin.user_management.index', [
            'users' => $users,
            'regions' => $regions,
            'accountTypes' => $accountTypes,
            'meterTypes' => $meterTypes,
        ]);
    }

    /**
     * Get user data with all related entities for editing
     */
    public function getUserData($id)
    {
        $user = User::with([
            'sites.accounts.meters.readings' => function($query) {
                $query->orderBy('reading_date', 'desc')->limit(5);
            },
            'sites.region'
        ])->find($id);
        
        if (!$user) {
            return response()->json(['status' => 404, 'message' => 'User not found']);
        }
        
        return response()->json(['status' => 200, 'data' => $user]);
    }

    /**
     * Store a new user with all related data
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact_number' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        DB::beginTransaction();
        
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'password' => Hash::make($request->password),
                'is_admin' => 0,
            ]);
            
            // Process sites if provided
            if ($request->has('sites') && is_array($request->sites)) {
                $this->processSites($user->id, $request->sites);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200, 
                'message' => 'User created successfully',
                'user_id' => $user->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500, 
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing user with all related data
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'contact_number' => 'required|string|max:20',
        ]);

        DB::beginTransaction();
        
        try {
            $user = User::findOrFail($id);
            
            // Update user basic info
            $user->name = $request->name;
            $user->email = $request->email;
            $user->contact_number = $request->contact_number;
            
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            
            $user->save();
            
            // Process sites if provided
            if ($request->has('sites') && is_array($request->sites)) {
                $this->processSites($user->id, $request->sites, true);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200, 
                'message' => 'User updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500, 
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user and all related data
     */
    public function destroy($id)
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
     * Generate a complete test user
     */
    public function generateTestUser()
    {
        DB::beginTransaction();
        
        try {
            // Generate unique test email
            $timestamp = time();
            $email = "testuser_{$timestamp}@test.com";
            
            // Create test user
            $user = User::create([
                'name' => 'Test User ' . $timestamp,
                'email' => $email,
                'contact_number' => '0' . rand(100000000, 999999999),
                'password' => Hash::make('password123'),
                'is_admin' => 0,
            ]);
            
            // Get first region and account type
            $region = Regions::first();
            $accountType = AccountType::first();
            $waterMeterType = MeterType::where('title', 'water')->first();
            $electricityMeterType = MeterType::where('title', 'electricity')->first();
            
            if (!$region) {
                throw new \Exception('No regions found. Please create a region first.');
            }
            
            // Create test site
            $site = Site::create([
                'user_id' => $user->id,
                'title' => 'Test Site ' . $timestamp,
                'lat' => -33.9249,
                'lng' => 18.4241,
                'address' => '123 Test Street, Cape Town, 8001',
                'email' => $email,
                'region_id' => $region->id,
                'billing_type' => 'monthly',
                'site_username' => 'testsite_' . $timestamp,
            ]);
            
            // Create test account
            $account = Account::create([
                'site_id' => $site->id,
                'account_name' => 'Test Account',
                'account_number' => 'ACC-' . $timestamp,
                'billing_date' => now()->format('Y-m-d'),
                'region_id' => $region->id,
                'account_type_id' => $accountType ? $accountType->id : null,
                'bill_day' => 15,
                'read_day' => 1,
            ]);
            
            // Create water meter with sample reading
            if ($waterMeterType) {
                $waterMeter = Meter::create([
                    'account_id' => $account->id,
                    'meter_type_id' => $waterMeterType->id,
                    'meter_category_id' => 1,
                    'meter_title' => 'Water Meter',
                    'meter_number' => 'WM-' . $timestamp,
                ]);
                
                // Add sample reading (46 KL for testing tiered calculations)
                MeterReadings::create([
                    'meter_id' => $waterMeter->id,
                    'reading_date' => now()->subMonth()->format('Y-m-d'),
                    'reading_value' => 1000,
                ]);
                
                MeterReadings::create([
                    'meter_id' => $waterMeter->id,
                    'reading_date' => now()->format('Y-m-d'),
                    'reading_value' => 1046, // 46 KL usage
                ]);
            }
            
            // Create electricity meter with sample reading
            if ($electricityMeterType) {
                $electricityMeter = Meter::create([
                    'account_id' => $account->id,
                    'meter_type_id' => $electricityMeterType->id,
                    'meter_category_id' => 1,
                    'meter_title' => 'Electricity Meter',
                    'meter_number' => 'EM-' . $timestamp,
                ]);
                
                // Add sample reading (500 KWH for testing tiered calculations)
                MeterReadings::create([
                    'meter_id' => $electricityMeter->id,
                    'reading_date' => now()->subMonth()->format('Y-m-d'),
                    'reading_value' => 5000,
                ]);
                
                MeterReadings::create([
                    'meter_id' => $electricityMeter->id,
                    'reading_date' => now()->format('Y-m-d'),
                    'reading_value' => 5500, // 500 KWH usage
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'Test user created successfully',
                'user_id' => $user->id,
                'email' => $email
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Error generating test user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all test users (emails ending with @test.com)
     */
    public function deleteTestUsers()
    {
        DB::beginTransaction();
        
        try {
            $testUsers = User::where('email', 'like', '%@test.com')->get();
            $count = $testUsers->count();
            
            foreach ($testUsers as $user) {
                $user->delete(); // Cascade delete handled by model
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => "Deleted {$count} test user(s) successfully"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Error deleting test users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone an existing user for testing
     */
    public function cloneUser($id)
    {
        DB::beginTransaction();
        
        try {
            $originalUser = User::with([
                'sites.accounts.meters'
            ])->findOrFail($id);
            
            $timestamp = time();
            
            // Create cloned user
            $newUser = User::create([
                'name' => $originalUser->name . ' (Clone)',
                'email' => 'clone_' . $timestamp . '@test.com',
                'contact_number' => $originalUser->contact_number,
                'password' => Hash::make('password123'),
                'is_admin' => 0,
            ]);
            
            // Clone sites
            foreach ($originalUser->sites as $site) {
                $newSite = Site::create([
                    'user_id' => $newUser->id,
                    'title' => $site->title . ' (Clone)',
                    'lat' => $site->lat,
                    'lng' => $site->lng,
                    'address' => $site->address,
                    'email' => $site->email,
                    'region_id' => $site->region_id,
                    'billing_type' => $site->billing_type,
                    'site_username' => $site->site_username ? $site->site_username . '_clone' : null,
                ]);
                
                // Clone accounts
                foreach ($site->accounts as $account) {
                    $newAccount = Account::create([
                        'site_id' => $newSite->id,
                        'account_name' => $account->account_name,
                        'account_number' => $account->account_number . '-CLONE',
                        'billing_date' => $account->billing_date,
                        'region_id' => $account->region_id,
                        'account_type_id' => $account->account_type_id,
                        'bill_day' => $account->bill_day,
                        'read_day' => $account->read_day,
                    ]);
                    
                    // Clone meters (without readings)
                    foreach ($account->meters as $meter) {
                        Meter::create([
                            'account_id' => $newAccount->id,
                            'meter_type_id' => $meter->meter_type_id,
                            'meter_category_id' => $meter->meter_category_id,
                            'meter_title' => $meter->meter_title,
                            'meter_number' => $meter->meter_number . '-CLONE',
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 200,
                'message' => 'User cloned successfully',
                'user_id' => $newUser->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Error cloning user: ' . $e->getMessage()
            ], 500);
        }
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
     * Process sites for user create/update
     */
    private function processSites($userId, $sites, $isUpdate = false)
    {
        // Track processed site IDs for update mode
        $processedSiteIds = [];
        
        foreach ($sites as $siteData) {
            $siteId = $siteData['id'] ?? null;
            
            $siteFields = [
                'user_id' => $userId,
                'title' => $siteData['title'] ?? '',
                'lat' => $siteData['lat'] ?? 0,
                'lng' => $siteData['lng'] ?? 0,
                'address' => $siteData['address'] ?? '',
                'email' => $siteData['email'] ?? null,
                'region_id' => $siteData['region_id'] ?? null,
                'billing_type' => $siteData['billing_type'] ?? 'monthly',
                'site_username' => $siteData['site_username'] ?? null,
            ];
            
            if ($siteId && $isUpdate) {
                // Update existing site
                $site = Site::find($siteId);
                if ($site && $site->user_id == $userId) {
                    $site->update($siteFields);
                } else {
                    $site = Site::create($siteFields);
                    $siteId = $site->id;
                }
            } else {
                // Create new site
                $site = Site::create($siteFields);
                $siteId = $site->id;
            }
            
            $processedSiteIds[] = $siteId;
            
            // Process accounts for this site
            if (isset($siteData['accounts']) && is_array($siteData['accounts'])) {
                $this->processAccounts($siteId, $siteData['accounts'], $isUpdate);
            }
        }
        
        // If updating, delete sites not in the processed list
        if ($isUpdate) {
            Site::where('user_id', $userId)
                ->whereNotIn('id', $processedSiteIds)
                ->get()
                ->each(function($site) {
                    $site->delete();
                });
        }
    }

    /**
     * Process accounts for site create/update
     */
    private function processAccounts($siteId, $accounts, $isUpdate = false)
    {
        $processedAccountIds = [];
        
        foreach ($accounts as $accountData) {
            $accountId = $accountData['id'] ?? null;
            
            $accountFields = [
                'site_id' => $siteId,
                'account_name' => $accountData['account_name'] ?? '',
                'account_number' => $accountData['account_number'] ?? '',
                'billing_date' => $accountData['billing_date'] ?? null,
                'region_id' => $accountData['region_id'] ?? null,
                'account_type_id' => $accountData['account_type_id'] ?? null,
                'bill_day' => $accountData['bill_day'] ?? null,
                'read_day' => $accountData['read_day'] ?? null,
            ];
            
            if ($accountId && $isUpdate) {
                $account = Account::find($accountId);
                if ($account && $account->site_id == $siteId) {
                    $account->update($accountFields);
                } else {
                    $account = Account::create($accountFields);
                    $accountId = $account->id;
                }
            } else {
                $account = Account::create($accountFields);
                $accountId = $account->id;
            }
            
            $processedAccountIds[] = $accountId;
            
            // Process meters for this account
            if (isset($accountData['meters']) && is_array($accountData['meters'])) {
                $this->processMeters($accountId, $accountData['meters'], $isUpdate);
            }
        }
        
        // If updating, delete accounts not in the processed list
        if ($isUpdate) {
            Account::where('site_id', $siteId)
                ->whereNotIn('id', $processedAccountIds)
                ->get()
                ->each(function($account) {
                    $account->delete();
                });
        }
    }

    /**
     * Process meters for account create/update
     */
    private function processMeters($accountId, $meters, $isUpdate = false)
    {
        $processedMeterIds = [];
        
        foreach ($meters as $meterData) {
            $meterId = $meterData['id'] ?? null;
            
            $meterFields = [
                'account_id' => $accountId,
                'meter_type_id' => $meterData['meter_type_id'] ?? null,
                'meter_category_id' => $meterData['meter_category_id'] ?? 1,
                'meter_title' => $meterData['meter_title'] ?? '',
                'meter_number' => $meterData['meter_number'] ?? '',
            ];
            
            if ($meterId && $isUpdate) {
                $meter = Meter::find($meterId);
                if ($meter && $meter->account_id == $accountId) {
                    $meter->update($meterFields);
                } else {
                    $meter = Meter::create($meterFields);
                    $meterId = $meter->id;
                }
            } else {
                $meter = Meter::create($meterFields);
                $meterId = $meter->id;
            }
            
            $processedMeterIds[] = $meterId;
            
            // Process sample reading if provided
            if (isset($meterData['sample_reading']) && !empty($meterData['sample_reading'])) {
                MeterReadings::create([
                    'meter_id' => $meterId,
                    'reading_date' => now()->format('Y-m-d'),
                    'reading_value' => $meterData['sample_reading'],
                ]);
            }
        }
        
        // If updating, delete meters not in the processed list
        if ($isUpdate) {
            Meter::where('account_id', $accountId)
                ->whereNotIn('id', $processedMeterIds)
                ->get()
                ->each(function($meter) {
                    $meter->delete();
                });
        }
    }
}
