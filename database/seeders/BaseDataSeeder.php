<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BaseDataSeeder extends Seeder
{
    /**
     * Seed base reference data:
     * - Meter Types
     * - Meter Categories  
     * - Region (Durban)
     * - Tariff Template (with tiers)
     * 
     * Then use Admin Panel to create Users (who select a tariff template)
     */
    public function run()
    {
        $this->command->info('========================================');
        $this->command->info('  Base Data Seeder');
        $this->command->info('========================================');
        $this->command->info('');

        // Step 1: Meter Types
        $this->command->info('[1/4] Creating Meter Types...');
        
        $waterTypeId = DB::table('meter_types')->where('title', 'Water')->value('id');
        $electricityTypeId = DB::table('meter_types')->where('title', 'Electricity')->value('id');
        
        if (!$waterTypeId) {
            $waterTypeId = DB::table('meter_types')->insertGetId([
                'title' => 'Water',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("   Created: Water (ID: $waterTypeId)");
        } else {
            $this->command->info("   Exists: Water (ID: $waterTypeId)");
        }
        
        if (!$electricityTypeId) {
            $electricityTypeId = DB::table('meter_types')->insertGetId([
                'title' => 'Electricity',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("   Created: Electricity (ID: $electricityTypeId)");
        } else {
            $this->command->info("   Exists: Electricity (ID: $electricityTypeId)");
        }

        // Step 2: Meter Categories
        $this->command->info('[2/4] Creating Meter Categories...');
        
        $categories = [
            ['meter_type_id' => $waterTypeId, 'title' => 'Water in'],
            ['meter_type_id' => $waterTypeId, 'title' => 'Water out'],
            ['meter_type_id' => $electricityTypeId, 'title' => 'Electricity'],
        ];
        
        foreach ($categories as $cat) {
            $exists = DB::table('meter_categories')
                ->where('title', $cat['title'])
                ->exists();
            if (!$exists) {
                DB::table('meter_categories')->insert([
                    'meter_type_id' => $cat['meter_type_id'],
                    'title' => $cat['title'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("   Created: {$cat['title']}");
            } else {
                $this->command->info("   Exists: {$cat['title']}");
            }
        }

        // Step 3: Region (Durban)
        $this->command->info('[3/4] Creating Region...');
        
        $regionId = DB::table('regions')->where('name', 'like', '%Durban%')->value('id');
        
        if (!$regionId) {
            $regionId = DB::table('regions')->insertGetId([
                'name' => 'Durban (eThekwini)',
                'electricity_base_unit_cost' => 2.2425,
                'electricity_base_unit' => 'kWh',
                'water_base_unit_cost' => 25.50,
                'water_base_unit' => 'kL',
                'water_email' => 'eservices@durban.gov.za',
                'electricity_email' => 'electricity@durban.gov.za',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("   Created: Durban (eThekwini) (ID: $regionId)");
        } else {
            $this->command->info("   Exists: Durban (eThekwini) (ID: $regionId)");
        }

        // Step 4: Tariff Template with Tiers
        $this->command->info('[4/4] Creating Tariff Template...');
        
        $tariffId = DB::table('regions_account_type_costs')
            ->where('region_id', $regionId)
            ->where('template_name', 'like', '%Durban%Residential%')
            ->value('id');
        
        if (!$tariffId) {
            $tariffId = DB::table('regions_account_type_costs')->insertGetId([
                'region_id' => $regionId,
                'template_name' => 'Durban Residential Water Tariff 2024/25',
                'billing_type' => 'MONTHLY',
                'is_water' => true,
                'vat_rate' => 15.00,
                'billing_day' => 24,
                'read_day' => 5,
                'is_active' => true,
                'effective_from' => '2024-07-01',
                'effective_to' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("   Created: Tariff Template (ID: $tariffId)");
            
            // Create Tariff Tiers (Durban water rates per kL)
            $tiers = [
                ['tier' => 1, 'min' => 0, 'max' => 6000, 'rate' => 25.50],
                ['tier' => 2, 'min' => 6000, 'max' => 15000, 'rate' => 32.80],
                ['tier' => 3, 'min' => 15000, 'max' => 30000, 'rate' => 42.50],
                ['tier' => 4, 'min' => 30000, 'max' => null, 'rate' => 55.20],
            ];
            
            foreach ($tiers as $t) {
                DB::table('tariff_tiers')->insert([
                    'tariff_template_id' => $tariffId,
                    'tier_number' => $t['tier'],
                    'min_units' => $t['min'],
                    'max_units' => $t['max'],
                    'rate_per_unit' => $t['rate'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info("   Created: 4 pricing tiers");
        } else {
            $this->command->info("   Exists: Tariff Template (ID: $tariffId)");
        }

        // Summary
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('  Base Data Complete!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Region: Durban (eThekwini)');
        $this->command->info('Tariff: Durban Residential Water Tariff 2024/25');
        $this->command->info('');
        $this->command->info('Now go to Admin Panel:');
        $this->command->info('  User Account Setup > Create Test User');
        $this->command->info('');
        $this->command->info('User can have:');
        $this->command->info('  - 1 Tariff Template (selected at user level)');
        $this->command->info('  - Up to 2 Accounts');
        $this->command->info('  - Up to 2 Meters per Account');
        $this->command->info('');
    }
}
