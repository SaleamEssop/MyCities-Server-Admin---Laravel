# Copilot Instructions - MyCities Server Admin

## Server Environment

### Digital Ocean Droplet Specs
- **Plan**: Basic Premium Intel
- **CPU**: 1 vCPU
- **RAM**: 1 GB (IMPORTANT: Limited memory!)
- **Storage**: 25 GB
- **Bandwidth**: 1 TB
- **Path**: `/var/www/mycities.co.za`

### Memory Constraints
⚠️ **CRITICAL**: The server only has 1GB RAM. `npm run prod` will FAIL at ~88% due to memory issues.

**Solution**: Assets are pre-built in GitHub Actions. Do NOT run `npm run prod` on the server.

### Swap File
A 2GB swap file has been added to help with memory:
```bash
# Swap is located at /swapfile (2GB)
# To check: free -h
# To verify: sudo swapon --show
```

---

## Deployment

### How Deployment Works
1. Push/merge to `main` branch
2. GitHub Actions builds assets (has 7GB+ RAM)
3. Compiled JS/CSS committed to repo
4. Files deployed to server via SSH
5. Server runs Laravel commands only (no npm!)

### Deploy Workflow Location
`.github/workflows/deploy.yml`

### Server Commands (Post-Deploy)
```bash
cd /var/www/mycities.co.za
sudo chown -R www-data:www-data /var/www/mycities.co.za
sudo chmod -R 755 /var/www/mycities.co.za
sudo chmod -R 775 storage bootstrap/cache
php artisan migrate --force --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
```

---

## Git Configuration

### Remote URL (SSH - Required!)
The server uses SSH authentication (not HTTPS):
```bash
git remote set-url origin git@github.com:SaleamEssop/MyCities-Server-Admin---Laravel.git
```

⚠️ **Do NOT use HTTPS** - it will prompt for username/password and fail.

---

## Key Features

### User Management (`/admin/user-management`)
All-in-one screen for managing:
- Users → Sites → Accounts → Meters (nested)
- Search by name, address, phone
- Filter: All / Real Users / Test Users
- Generate Test User (creates complete setup with sample readings)
- Delete All Test Data (removes @test.com users)
- Clone User functionality

### Tariff Templates (`/admin/tariff_template`)
- Water and Electricity tiered pricing
- Dynamic tier rows (add/remove)
- Real-time calculations
- Bill Preview section

---

## Tech Stack

- **Framework**: Laravel 8.x
- **Frontend**: Vue 3 + Bootstrap 5
- **Build Tool**: Laravel Mix (Webpack)
- **Database**: MySQL
- **Server**: Nginx on Digital Ocean

---

## Common Issues & Fixes

### Issue: `npm run prod` fails at 88%
**Cause**: 1GB RAM is not enough for Webpack
**Fix**: Assets are pre-built in GitHub Actions. Don't run on server.

### Issue: Git asks for username/password
**Cause**: Using HTTPS remote URL
**Fix**: 
```bash
git remote set-url origin git@github.com:SaleamEssop/MyCities-Server-Admin---Laravel.git
```

### Issue: Permission denied errors
**Fix**:
```bash
sudo chown -R www-data:www-data /var/www/mycities.co.za
sudo chmod -R 755 /var/www/mycities.co.za
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: Corrupted git objects
**Fix**:
```bash
rm -rf .git/objects/pack
rm -rf .git/objects/*
git fetch --all
git reset --hard origin/main
```

### Issue: Deploy timeout
**Cause**: SSH action default timeout is 10 minutes
**Fix**: `command_timeout: 30m` in deploy.yml

---

## Manual Server Commands

### If you need to build manually (emergency only):
```bash
cd /var/www/mycities.co.za

# Use swap + limited memory
NODE_OPTIONS="--max-old-space-size=512" npm run prod
```

### SSH into server:
```bash
ssh root@<DO_HOST>
```

---

## GitHub Secrets Required

- `DO_SSH_KEY` - Private SSH key for server access
- `DO_HOST` - Server IP address
- `DO_USERNAME` - SSH username (usually `root`)

---

## Important Files

| File | Purpose |
|------|---------|
| `.github/workflows/deploy.yml` | Auto-deploy on push to main |
| `resources/js/components/` | Vue 3 components |
| `app/Http/Controllers/` | Laravel controllers |
| `routes/web.php` | All routes |
| `public/js/app.js` | Compiled JS (pre-built) |
| `public/css/app.css` | Compiled CSS (pre-built) |

---

## Contact

Repository Owner: @SaleamEssop

---

*Last Updated: December 2025*