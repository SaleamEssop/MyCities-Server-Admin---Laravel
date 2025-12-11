<?php

// Suppress deprecation warnings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "========================================\n";
echo "  Database Reset\n";
echo "========================================\n\n";

try {
    // Disable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    // Get all tables
    $tables = DB::select('SHOW TABLES');
    $dbName = env('DB_DATABASE', 'mycities_local');
    $key = "Tables_in_" . $dbName;
    
    echo "Dropping all tables...\n";
    foreach ($tables as $table) {
        $tableName = $table->$key;
        echo "  Dropping: $tableName\n";
        DB::statement("DROP TABLE IF EXISTS `$tableName`");
    }
    
    // Re-enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\nAll tables dropped.\n\n";
    
} catch (Exception $e) {
    echo "Warning during drop: " . $e->getMessage() . "\n\n";
}

echo "Running migrations...\n\n";

// Run each migration manually to catch and skip errors
$migrationPath = __DIR__ . '/database/migrations';
$files = glob($migrationPath . '/*.php');
sort($files);

// First create migrations table
try {
    if (!Schema::hasTable('migrations')) {
        Schema::create('migrations', function ($table) {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });
        echo "Created migrations table\n";
    }
} catch (Exception $e) {
    echo "Migrations table: " . $e->getMessage() . "\n";
}

$batch = 1;
$successCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($files as $file) {
    $migrationName = basename($file, '.php');
    
    // Check if already run
    $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
    if ($exists) {
        $skipCount++;
        continue;
    }
    
    try {
        require_once $file;
        
        // Get class name from file
        $content = file_get_contents($file);
        if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
            $className = $matches[1];
            
            if (class_exists($className)) {
                $migration = new $className();
                $migration->up();
                
                // Record migration
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch
                ]);
                
                echo "✓ $migrationName\n";
                $successCount++;
            }
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
        
        // Skip "already exists" errors
        if (strpos($errorMsg, 'already exists') !== false || 
            strpos($errorMsg, 'Duplicate') !== false) {
            echo "~ $migrationName (skipped - already exists)\n";
            
            // Still record it as run
            DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $batch
            ]);
            $skipCount++;
        } else {
            echo "✗ $migrationName: $errorMsg\n";
            $errorCount++;
        }
    }
}

echo "\n========================================\n";
echo "  Migration Summary\n";
echo "========================================\n";
echo "Success: $successCount\n";
echo "Skipped: $skipCount\n";
echo "Errors:  $errorCount\n";
echo "\n";
