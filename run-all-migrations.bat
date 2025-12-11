@echo off
echo ===============================================
echo Running All Pending Migrations
echo ===============================================
echo.

cd /d "C:\MyCities\MyCities-Server-Admin---Laravel"

echo Current directory: %CD%
echo.

echo Step 1: Checking migration status...
php artisan migrate:status

echo.
echo Step 2: Running pending migrations...
php artisan migrate

echo.
echo ===============================================
echo Migration complete!
echo ===============================================
echo.

echo Step 3: Migration status after run...
php artisan migrate:status

echo.
pause




