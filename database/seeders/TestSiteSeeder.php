<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TestSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find an existing user or create one for testing
        $user = User::first();
        if (!$user) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'password' => bcrypt('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = $user->id;
        }

        DB::table('sites')->insert([
            'user_id' => $userId,
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
