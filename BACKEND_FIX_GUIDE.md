# Campus Event Hub Backend - Quick Fix Guide

## 🔴 Current Issues

1. **Backend Storage Permissions** - ✅ FIXED
2. **Database Connection Issue** - MySQL is not running or misconfigured
3. **API Not Responding Properly** - Once DB is fixed

## ✅ What We've Done

1. Improved mobile app error handling
2. Added API health check endpoint
3. Fixed all Laravel storage and cache permissions
4. Added better error messages

## 🔧 Fix the Database Connection

### Option 1: Use Laragon (Recommended)

If you're using **Laragon**, the easiest way:

1. **Open Laragon Menu**:
   - Click the Laragon icon in system tray
   - Go to **MySQL** → **Start**
   - Wait for it to show "Running"

2. **Restart Apache**:
   - In Laragon menu: **Apache** → **Stop**
   - Then: **Apache** → **Start**

3. **Test Backend**:
   - Open browser: `https://aseems.ddns.net/api/health`
   - Should show: `{"status":"ok","message":"API is running"}`

### Option 2: Manual MySQL Start

```cmd
# Open Command Prompt as Administrator

# If MySQL is installed via Laragon:
"C:\laragon\bin\mysql\mysql-8.0.22-winx64\bin\mysqld.exe" --defaults-file="C:\laragon\data\mysql\my.ini"

# Or if you installed MySQL separately:
net start MySQL
```

### Option 3: Check .env File

**File**: `c:\laragon\www\CampusEventHub\.env`

Make sure these settings are correct:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=campuseventhub
DB_USERNAME=root
DB_PASSWORD=
```

If you changed anything, restart MySQL after saving.

## 🧪 Test the Backend

After fixing MySQL:

1. **Test Health Endpoint**:
   ```
   https://aseems.ddns.net/api/health
   ```
   Should return:
   ```json
   {
     "status": "ok",
     "message": "API is running",
     "timestamp": "2026-04-12T..."
   }
   ```

2. **Test Registration** (from mobile app):
   - Try creating a new account
   - Should now work!

3. **Test Login**:
   - Login with the account you just created
   - Should show profile data

## 🚀 Complete Backend Setup Steps

If you need to set up from scratch:

```bash
cd c:\laragon\www\CampusEventHub

# 1. Install dependencies
composer install

# 2. Generate app key
php artisan key:generate

# 3. Create database
# - Open Laragon
# - Click "MySQL" → "Start"
# - Open phpMyAdmin
# - Create database named: campuseventhub

# 4. Run migrations
php artisan migrate --force

# 5. (Optional) Seed dummy data
php artisan db:seed

# 6. Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000
```

Or using Laragon's built-in server (easier):

1. Click "Start All" in Laragon
2. Open: `http://localhost/CampusEventHub/public`
3. API will be at: `http://localhost:8000/api` (if using artisan serve)

## 📱 Update Mobile App (if needed)

If you're running Laravel locally for testing:

**File**: `lib/services/api_service.dart` (line 11)

For **local testing**:
```dart
static const String baseUrl = 'http://192.168.1.100:8000/api';
```
(Replace `192.168.1.100` with your PC's IP)

For **your server** (current setting):
```dart
static const String baseUrl = 'https://aseems.ddns.net/api';
```

## ✅ Verification Checklist

- [ ] Laragon running (all services green)
- [ ] MySQL service is running
- [ ] API health endpoint responds with JSON
- [ ] Database tables exist (check with phpMyAdmin)
- [ ] Mobile app can register new account
- [ ] Mobile app can login
- [ ] Profile shows user data

## 🐛 Troubleshooting

### Browser shows "Your connection is not private"
- **Cause**: The HTTPS certificate for `aseems.ddns.net` has expired.
- **Observed on**: `CN=aseems.ddns.net`, issued by `Let's Encrypt`, expired on `2026-05-06`.
- **Fix**: Renew the certificate on the Ubuntu/nginx server, then reload nginx.

```bash
sudo certbot renew --nginx
sudo nginx -t
sudo systemctl reload nginx
```

If renewal does not pick up the domain automatically, issue a new cert:

```bash
sudo certbot --nginx -d aseems.ddns.net
```

### "Connection refused" error
- **Fix**: Start MySQL in Laragon (MySQL → Start)

### "Access denied for user 'root'"
- **Fix**: Check `.env` file for correct DB_USERNAME and DB_PASSWORD

### "Database doesn't exist"
- **Fix**: Create database in phpMyAdmin or run: `php artisan migrate --force`

### Still getting HTML error
- **Fix**: 
  1. Check Laravel logs: `storage/logs/laravel.log`
  2. Restart both Apache and MySQL in Laragon
  3. Run: `php artisan cache:clear && php artisan config:clear`

### Mobile app still times out
- **Fix**: 
  1. Verify API URL is correct in `lib/services/api_service.dart`
  2. Check firewall isn't blocking HTTPS connections
  3. Verify backend server is accessible with ping/curl

## 📋 Common Database Issues

### User table missing
```bash
php artisan migrate --force
```

### Permission denied on storage
```bash
php fix-permissions.php  # Already done ✓
```

### Cache corruption
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

## 🎯 Next Steps

1. **Start MySQL** in Laragon
2. **Test API**: Open `https://aseems.ddns.net/api/health` in browser
3. **Rebuild app**: `flutter clean && flutter run`
4. **Try registering** again in the mobile app

---

**Need more help?** Check the Laravel logs:
```
c:\laragon\www\CampusEventHub\storage\logs\laravel.log
```

Last updated: April 12, 2026
