<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sites')->insert([
            'user_id' => 1,
            'title' => 'Test Site',
            'lat' => '-26.2041',
            'lng' => '28.0473',
            'address' => '123 Test Street, Johannesburg, South Africa',
            'email' => 'testsite@example.com',
            'billing_type' => 'date_to_date',
            'site_username' => 'test_site_user',
            'site_password' => bcrypt('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
