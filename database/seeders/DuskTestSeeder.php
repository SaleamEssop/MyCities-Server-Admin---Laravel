<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Regions;
use App\Models\AccountType;
use App\Models\Site;
use App\Models\Account;
use App\Models\Meter;
use App\Models\MeterType;
use App\Models\MeterCategory;

/**
 * Seeder for Dusk browser tests.
 * Creates all necessary test data for comprehensive admin panel testing.
 */
class DuskTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create test admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
            ]
        );

        // Create additional test user for CRUD tests
        $testUser = User::firstOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'Test User',
                'contact_number' => '0123456789',
                'password' => Hash::make('password123'),
            ]
        );

        // Create test regions
        $region = Regions::firstOrCreate(
            ['name' => 'Test Region'],
            [
                'electricity_base_unit_cost' => 1.50,
                'electricity_base_unit' => 'kWh',
                'water_base_unit_cost' => 25.00,
                'water_base_unit' => 'kL',
                'water_email' => 'water@test.com',
                'electricity_email' => 'electricity@test.com',
            ]
        );

        // Create test account types
        $accountType = AccountType::firstOrCreate(
            ['type' => 'Residential'],
            ['is_active' => 1]
        );

        AccountType::firstOrCreate(
            ['type' => 'Commercial'],
            ['is_active' => 1]
        );

        // Create meter types if they don't exist
        $waterMeterType = MeterType::firstOrCreate(
            ['title' => 'Water'],
            ['title' => 'Water']
        );
        
        $electricityMeterType = MeterType::firstOrCreate(
            ['title' => 'Electricity'],
            ['title' => 'Electricity']
        );

        // Create meter categories if they don't exist
        $meterCategory = MeterCategory::firstOrCreate(
            ['title' => 'Standard'],
            ['title' => 'Standard']
        );

        // Create test site
        $site = Site::firstOrCreate(
            ['title' => 'Test Site'],
            [
                'user_id' => $testUser->id,
                'lat' => '-26.2041',
                'lng' => '28.0473',
                'address' => '123 Test Street, Johannesburg',
                'email' => 'testsite@example.com',
                'region_id' => $region->id,
                'billing_type' => 'date_to_date',
                'site_username' => 'test_site',
            ]
        );

        // Create test account
        $account = Account::firstOrCreate(
            ['account_number' => 'TEST-001'],
            [
                'site_id' => $site->id,
                'account_name' => 'Test Account',
                'billing_date' => now()->format('Y-m-d'),
                'region_id' => $region->id,
                'account_type_id' => $accountType->id,
            ]
        );

        // Create test meters
        Meter::firstOrCreate(
            ['meter_number' => 'WM-001'],
            [
                'account_id' => $account->id,
                'meter_category_id' => $meterCategory->id,
                'meter_type_id' => $waterMeterType->id,
                'meter_title' => 'Water Meter 1',
            ]
        );

        Meter::firstOrCreate(
            ['meter_number' => 'EM-001'],
            [
                'account_id' => $account->id,
                'meter_category_id' => $meterCategory->id,
                'meter_type_id' => $electricityMeterType->id,
                'meter_title' => 'Electricity Meter 1',
            ]
        );
    }
}
