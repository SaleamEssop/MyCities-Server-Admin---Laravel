@echo off
echo ========================================
echo Laravel Full Fix Script
echo ========================================
echo.

echo [1/5] Removing corrupt vendor folder...
rmdir /s /q vendor 2>nul
del /f /q vendor 2>nul
echo Done.

echo [2/5] Creating storage directories...
mkdir storage\framework\views 2>nul
mkdir storage\framework\sessions 2>nul
mkdir storage\framework\cache 2>nul
mkdir storage\logs 2>nul
mkdir bootstrap\cache 2>nul
echo Done.

echo [3/5] Running composer install...
composer install --no-interaction --ignore-platform-reqs
echo Done.

echo [4/5] Generating app key...
php artisan key:generate --force
echo Done.

echo [5/5] Starting Laravel server on port 9000...
php artisan serve --port=9000




