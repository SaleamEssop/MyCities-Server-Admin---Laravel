@echo off
echo Creating Laravel storage directories...
mkdir storage\framework\views 2>nul
mkdir storage\framework\sessions 2>nul
mkdir storage\framework\cache 2>nul
mkdir storage\logs 2>nul
mkdir bootstrap\cache 2>nul
echo Done!
echo.
echo Now running artisan commands...
php artisan key:generate --force
php artisan config:clear
php artisan cache:clear
echo.
echo Starting Laravel server on port 9000...
php artisan serve --port=9000





