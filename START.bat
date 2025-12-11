@echo off
echo Setting up and starting Laravel...
if not exist .env copy .env.example .env
php artisan key:generate --force
echo.
echo Starting Laravel server on port 9000...
php artisan serve --port=9000




