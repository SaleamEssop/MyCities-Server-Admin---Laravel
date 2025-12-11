# MyCities Production Deployment Guide

## Overview

This guide covers deploying MyCities to **www.mycities.co.za**.

## Architecture

```
www.mycities.co.za/
├── /              → Landing page (Laravel Blade)
├── /web-app/      → Vue/Quasar SPA
├── /admin/        → Admin Panel (Laravel Blade)
└── /api/v1/       → REST API (Laravel)
```

## Pre-Deployment Checklist

### 1. Server Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js 16+ (for building Vue app)
- Apache/Nginx with mod_rewrite enabled

### 2. Laravel Configuration

Create/update `.env` file on production server:

```env
APP_NAME=MyCities
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://www.mycities.co.za

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mycities_production
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

SESSION_DRIVER=file
CACHE_DRIVER=file

MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=noreply@mycities.co.za
MAIL_FROM_NAME="MyCities"
```

### 3. Generate App Key
```bash
php artisan key:generate
```

## Deployment Steps

### Step 1: Deploy Laravel Backend

```bash
# On production server
cd /var/www/mycities.co.za

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 2: Build Vue/Quasar App

On your local machine (or CI/CD):

```bash
cd C:\MyCities\MyCities-Vue-Quasar

# Install dependencies
npm install

# Build production SPA
npm run build:spa

# Deploy to Laravel public folder
npm run deploy:local
```

This copies the built app to `MyCities-Server-Admin---Laravel/public/web-app/`.

### Step 3: Upload to Server

Option A: Git (if web-app is committed)
```bash
git add public/web-app
git commit -m "Update web-app build"
git push origin main
```

Option B: Direct upload
```bash
# Upload dist/spa contents to server's public/web-app/
scp -r dist/spa/* user@mycities.co.za:/var/www/mycities.co.za/public/web-app/
```

## Apache Configuration

Create `/etc/apache2/sites-available/mycities.co.za.conf`:

```apache
<VirtualHost *:80>
    ServerName www.mycities.co.za
    ServerAlias mycities.co.za
    DocumentRoot /var/www/mycities.co.za/public

    <Directory /var/www/mycities.co.za/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mycities_error.log
    CustomLog ${APACHE_LOG_DIR}/mycities_access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite mycities.co.za.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

## Nginx Configuration (Alternative)

```nginx
server {
    listen 80;
    server_name www.mycities.co.za mycities.co.za;
    root /var/www/mycities.co.za/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /web-app {
        try_files $uri $uri/ /web-app/index.html;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## SSL Certificate (HTTPS)

Using Certbot:
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d www.mycities.co.za -d mycities.co.za
```

## URL Structure After Deployment

| URL | Description |
|-----|-------------|
| `https://www.mycities.co.za` | Landing page |
| `https://www.mycities.co.za/web-app` | Vue/Quasar app |
| `https://www.mycities.co.za/admin` | Admin panel |
| `https://www.mycities.co.za/api/v1/*` | API endpoints |

## Troubleshooting

### 500 Error
- Check `storage/logs/laravel.log`
- Ensure APP_DEBUG=false in production
- Verify file permissions

### Blank Page on /web-app
- Check browser console for errors
- Verify `public/web-app/index.html` exists
- Clear browser cache

### API Connection Failed
- Verify API_URL in Vue app matches production domain
- Check CORS configuration in Laravel
- Verify database connection

## Maintenance Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# Check application health
php artisan about

# Database backup
mysqldump -u user -p mycities_production > backup_$(date +%Y%m%d).sql
```

## Rollback Procedure

```bash
# Revert to previous commit
git reset --hard HEAD~1
git push --force origin main

# Or restore from backup
git checkout <previous-commit-hash>
```

