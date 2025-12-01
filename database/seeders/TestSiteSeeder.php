<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class TestSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $existingSite = Site::where('site_username', 'test_site')->first();
        
        if (!$existingSite) {
            Site::create([
                'site_username' => 'test_site',
                'site_password' => bcrypt('password123'),
                'billing_type' => 'date_to_date',
                'title' => 'Test Site',
                'address' => '123 Test Street',
                'region_id' => 1,
                'user_id' => 1,
                'lat' => '0.0',
                'lng' => '0.0',
            ]);
        }
    }
}
