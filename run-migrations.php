<?php

// Suppress deprecation warnings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "========================================\n";
echo "  Running Migrations\n";
echo "========================================\n\n";

try {
    // Run migrate:fresh to drop all tables and re-run migrations
    $exitCode = Artisan::call('migrate:fresh', ['--force' => true]);
    echo Artisan::output();
    
    if ($exitCode === 0) {
        echo "\n✓ Migrations completed successfully!\n";
    } else {
        echo "\n✗ Migrations had issues (exit code: $exitCode)\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nTrying individual migration...\n";
    
    // Try regular migrate as fallback
    try {
        $exitCode = Artisan::call('migrate', ['--force' => true]);
        echo Artisan::output();
    } catch (Exception $e2) {
        echo "Fallback also failed: " . $e2->getMessage() . "\n";
    }
}

echo "\n";




