<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Creating admin user...\n";

try {
    // Delete existing admin if exists
    User::where('email', 'admin@mycities.local')->delete();
    
    // Create new admin
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@mycities.local',
        'password' => Hash::make('admin123'),
        'contact_number' => '0000000000',
        'email_verified_at' => now(),
    ]);
    
    echo "\n";
    echo "========================================\n";
    echo "  Admin User Created Successfully!\n";
    echo "========================================\n";
    echo "\n";
    echo "Login at: http://localhost:9000/admin/login\n";
    echo "Email:    admin@mycities.local\n";
    echo "Password: admin123\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
