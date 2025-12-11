<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = $argv[1] ?? 'testuser1765326135@test.com';
$newPassword = $argv[2] ?? 'password123';

echo "Resetting password for: $email\n";

$user = User::where('email', $email)->first();

if ($user) {
    $user->password = Hash::make($newPassword);
    $user->save();
    
    echo "\n========================================\n";
    echo "  Password Reset Successful!\n";
    echo "========================================\n\n";
    echo "Email:    $email\n";
    echo "Password: $newPassword\n\n";
} else {
    echo "User not found: $email\n";
}




