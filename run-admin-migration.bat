@echo off
echo ===============================================
echo Running Admin Actions Migration
echo ===============================================
echo.

cd /d "C:\MyCities\MyCities-Server-Admin---Laravel"

echo Current directory: %CD%
echo.

echo Running migration...
php artisan migrate --path=database/migrations/2025_12_10_000001_create_admin_actions_table.php

if errorlevel 1 (
    echo.
    echo Migration failed! Trying to run all pending migrations...
    php artisan migrate
)

echo.
echo ===============================================
echo Migration complete!
echo ===============================================
echo.
pause





