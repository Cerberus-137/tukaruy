# 🚨 URGENT FIX - API Error "Unexpected token '<'"

## ⚠️ MASALAH UTAMA

**Error:** `Error: Unexpected token '<'. * Try again.`

**Root Cause:** 
API endpoint `/api/search` mengembalikan **HTML** bukan **JSON** karena:
1. `.htaccess` di root me-rewrite semua request
2. File `search.php` tidak ter-eksekusi
3. Server mengembalikan 404 page (HTML) yang kemudian di-parse sebagai JSON

**BUKAN karena edit ship date button!** Ship date button tidak ada hubungannya dengan error ini.

---

## ✅ SOLUSI - 3 File Harus Di-Upload

### File 1: `.htaccess` (ROOT) - CRITICAL!
**Path:** `/.htaccess`

**Isi baru:**
```apache
RewriteEngine On

# Redirect to HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove .php extension for non-API files ONLY
# BUT skip if path starts with api/ or admin/api/
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteCond %{REQUEST_URI} !^/api/
RewriteCond %{REQUEST_URI} !^/admin/api/
RewriteRule ^(.*)$ $1.php [L]

# Enable CORS for API endpoints
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory browsing
Options -Indexes

# Protect config file
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

**Yang berubah:** 
- RewriteCond sekarang di ATAS RewriteRule
- Tambah kondisi `!^/api/` dan `!^/admin/api/`

---

### File 2: `api/.htaccess` (NEW FILE!)
**Path:** `/api/.htaccess`

**Isi:**
```apache
# Force no rewrite in API directory
# All PHP files must execute directly

# Disable any parent rewrite rules
RewriteEngine Off

# Force JSON content type for .php files
<FilesMatch "\.php$">
    Header set Content-Type "application/json"
</FilesMatch>
```

**Kenapa butuh ini?**
- Memastikan semua rewrite rule dari parent di-disable
- Force semua .php file execute langsung
- Set content-type JSON secara otomatis

---

### File 3: `admin/api/.htaccess` (NEW FILE!)
**Path:** `/admin/api/.htaccess`

**Isi:** (sama seperti File 2)
```apache
# Force no rewrite in Admin API directory
# All PHP files must execute directly

# Disable any parent rewrite rules
RewriteEngine Off

# Force JSON content type for .php files
<FilesMatch "\.php$">
    Header set Content-Type "application/json"
</FilesMatch>
```

---

### File 4: `api/search.php` (UPDATE)
**Path:** `/api/search.php`

**Sudah ditambahkan:**
- Debug logging yang SANGAT detail
- Buffer control untuk prevent HTML output
- Log "🚨 API SEARCH.PHP EXECUTED!" di server log

---

## 🧪 CARA TEST SETELAH UPLOAD

### Test 1: Cek File API Accessible
Buka di browser:
```
https://www.tukarkuy.web.id/api/test.php
```

**✅ HARUS muncul JSON:**
```json
{
    "success": true,
    "message": "API endpoint is working"
}
```

**❌ Jika muncul HTML atau 404:**
- `.htaccess` masih rewrite
- File tidak di-upload dengan benar
- Folder permission salah

---

### Test 2: Cek Server Log
Setelah akses `/api/test.php`, cek server error log.

**HARUS ada log ini:**
```
=================================================
🚨 API SEARCH.PHP EXECUTED!
🚨 THIS FILE IS BEING CALLED!
🚨 Time: 2026-07-05 12:34:56
=================================================
```

**Jika TIDAK ada log ini:**
- File `search.php` TIDAK ter-eksekusi
- Apache masih rewrite ke halaman lain
- `.htaccess` belum di-apply

---

### Test 3: Test Track Page
1. Buka https://www.tukarkuy.web.id/track
2. Buka Console (F12)
3. Klik "Search Tracking Numbers"
4. Lihat console:

**✅ Harus muncul:**
```
📡 Sending request to api/search
📥 Response status: 200
📥 Response content-type: application/json
✅ API Response received
```

**❌ Jika masih error:**
```
❌ Non-JSON response received
```
Berarti `.htaccess` belum fix.

---

## 🔍 DEBUG CHECKLIST

### Step 1: Verify Files Uploaded
SSH ke server, cek:
```bash
ls -la .htaccess
ls -la api/.htaccess
ls -la admin/api/.htaccess
ls -la api/search.php
```

Semua file harus ada!

---

### Step 2: Test Direct PHP Access
```bash
curl -I https://www.tukarkuy.web.id/api/search.php
```

**Harus return:**
```
HTTP/1.1 200 OK
Content-Type: application/json
```

**Jika 404 Not Found:**
- File tidak ter-upload
- Path salah

---

### Step 3: Check Apache Config
```bash
# Check if mod_rewrite enabled
apache2ctl -M | grep rewrite

# Check syntax
apache2ctl -t
```

Harus show: `rewrite_module (shared)`

---

### Step 4: Check Server Error Log
```bash
tail -f /var/log/apache2/error.log
# atau
tail -f /var/log/httpd/error_log
```

Cari log dengan emoji:
- 🚨 = File executed
- 🔍 = Request received
- ✅ = Success
- ❌ = Error

**Jika TIDAK ada emoji sama sekali:**
PHP file tidak ter-eksekusi!

---

## 💡 COMMON ISSUES

### Issue 1: "Still getting HTML response"

**Cause:** `.htaccess` tidak di-apply atau syntax salah

**Fix:**
1. Check file permission: `chmod 644 .htaccess`
2. Restart Apache: `sudo service apache2 restart`
3. Check AllowOverride in Apache config

---

### Issue 2: "403 Forbidden"

**Cause:** File permission salah

**Fix:**
```bash
chmod 755 api/
chmod 644 api/.htaccess
chmod 644 api/search.php
```

---

### Issue 3: "No logs in server"

**Cause:** 
- File tidak ter-eksekusi
- Log directory permission

**Fix:**
1. Check error_log directive in php.ini
2. Check log directory writable
3. Test with `error_log("TEST");` in search.php

---

## 📦 UPLOAD CHECKLIST

Upload file-file ini **SEKARANG**:

- [ ] `/.htaccess` (ROOT - update existing)
- [ ] `/api/.htaccess` (NEW FILE)
- [ ] `/admin/api/.htaccess` (NEW FILE)
- [ ] `/api/search.php` (update)
- [ ] `/api/test.php` (already created before)

---

## 🎯 EXPECTED RESULT

Setelah upload semua file:

1. ✅ `/api/test.php` return JSON
2. ✅ Server log show "🚨 API SEARCH.PHP EXECUTED!"
3. ✅ Track page load results tanpa error
4. ✅ Console show "application/json" response
5. ✅ No more "Unexpected token '<'" error

---

## 📞 IF STILL NOT WORKING

Jika setelah upload semua file masih error:

1. **Screenshot ini:**
   - Browser console saat error
   - Result dari `/api/test.php`
   - Server error log (last 50 lines)

2. **Test manual:**
   ```bash
   curl -X POST https://www.tukarkuy.web.id/api/search.php \
     -H "Content-Type: application/json" \
     -d '{"dest_country":"US"}'
   ```
   Copy paste hasilnya

3. **Check ini:**
   ```bash
   # Is .htaccess being read?
   ls -la .htaccess api/.htaccess admin/api/.htaccess
   
   # Apache config
   apache2ctl -M | grep rewrite
   
   # Server log
   tail -50 /var/log/apache2/error.log
   ```

---

**INGAT:** 
- Error ini **BUKAN** karena ship date button
- Error ini karena **`.htaccess` rewrite rules**
- Solusi: **3 file `.htaccess`** harus di-upload

**Upload sekarang, test, dan report hasilnya!**

---

Last Updated: July 5, 2026, 1:30 PM
Status: WAITING FOR FILE UPLOAD & TEST
