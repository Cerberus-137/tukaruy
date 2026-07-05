# API Error Fix - "Unexpected token '<'"

## 🔴 Error Yang Terjadi

**Error Message:** `Error: Unexpected token '<'. * Try again.`

**Penyebab:** 
- API endpoint mengembalikan HTML (<!DOCTYPE...) bukan JSON
- `.htaccess` sedang me-rewrite URL API sehingga PHP file tidak dieksekusi
- Request ke `/api/search` tidak sampai ke `search.php`

## ✅ Perbaikan Yang Sudah Dilakukan

### 1. Fixed `.htaccess` - Skip API Rewrite
**File:** `.htaccess`

**Old (SALAH):**
```apache
# Don't rewrite API endpoints - they need .php extension
RewriteCond %{REQUEST_URI} !^/api/
RewriteCond %{REQUEST_URI} !^/admin/api/

# Remove .php extension for non-API files
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteRule ^(.*)$ $1.php [L]
```

**New (BENAR):**
```apache
# Skip rewrite for API endpoints - they need .php extension
RewriteRule ^api/ - [L]
RewriteRule ^admin/api/ - [L]

# Remove .php extension for non-API files
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteRule ^(.*)$ $1.php [L]
```

**Penjelasan:**
- `RewriteRule ^api/ - [L]` = Skip rewrite untuk semua request yang mulai dengan `api/`
- `[L]` flag = Last rule, stop processing
- `-` = Pass through, jangan ubah apa-apa

### 2. Enhanced Error Logging di `api/search.php`
**File:** `api/search.php`

Added logging:
- 🔍 Request received
- 📋 Filters received
- 🔎 Processing filters
- ✅ Results found
- ❌ Errors

### 3. Better Error Handling di JavaScript
**File:** `assets/js/app.js`

- Check content-type sebelum parse JSON
- Tampilkan error message yang informatif jika dapat HTML
- Console log yang lebih detail

## 🧪 Cara Test

### Test 1: Verify API Endpoint Accessible
```bash
# Test basic API endpoint
curl https://www.tukarkuy.web.id/api/test.php
```

**Expected Response (JSON):**
```json
{
    "success": true,
    "message": "API endpoint is working",
    "timestamp": "2026-07-05 12:34:56",
    "request_method": "GET",
    "request_uri": "/api/test.php"
}
```

**If you get HTML:** `.htaccess` still rewriting!

### Test 2: Browser Test
1. Buka: https://www.tukarkuy.web.id/api/test.php
2. **Harus muncul JSON**, bukan HTML atau error 404
3. Jika muncul HTML = `.htaccess` masih salah

### Test 3: Track Page Test
1. Buka: https://www.tukarkuy.web.id/track
2. Buka Console (F12)
3. Lihat logs:
   ```
   🔍 Performing search with filters: {...}
   📡 Sending request to api/search
   📥 Response status: 200
   📥 Response content-type: application/json
   ✅ API Response received: {...}
   ```
4. Jika ada `❌ Non-JSON response received` = masih error

### Test 4: Check Server Logs
Cari log entries dengan emoji:
```
🔍 Search API Request - Method: POST, URI: /api/search
📋 Search API: Filters received - {"dest_country":"US","status":["pre-transit"]}
🔎 Search API: Processing filters - {"dest_country":"US","status":["pre-transit"]}
✅ Search API: Found 10 results
```

## 🔧 Troubleshooting

### Issue 1: Still Getting HTML Response

**Verify .htaccess is correct:**
```bash
cat .htaccess | grep -A 2 "Skip rewrite"
```

Should show:
```
# Skip rewrite for API endpoints
RewriteRule ^api/ - [L]
RewriteRule ^admin/api/ - [L]
```

**Test with direct .php access:**
```
https://www.tukarkuy.web.id/api/test.php
```

If this works but `/api/test` doesn't, .htaccess is the problem.

### Issue 2: 404 Not Found

API files missing or wrong path.

**Check files exist:**
```bash
ls -la api/
# Should show: search.php, test.php, etc.
```

### Issue 3: 500 Internal Server Error

PHP error in the API file.

**Check error logs:**
```bash
tail -f /path/to/error.log
```

Look for:
```
❌ Search API Error: [error message]
```

### Issue 4: Still Shows "Unexpected token '<'"

The fix hasn't been applied or cached.

**Solutions:**
1. Clear browser cache (Ctrl + Shift + R)
2. Test in Incognito/Private mode
3. Verify .htaccess was uploaded
4. Check Apache has mod_rewrite enabled

## 📝 Quick Fix Checklist

- [ ] Upload fixed `.htaccess`
- [ ] Upload fixed `api/search.php`
- [ ] Upload fixed `assets/js/app.js`
- [ ] Upload new `api/test.php`
- [ ] Clear browser cache
- [ ] Test `/api/test.php` endpoint
- [ ] Test track page filters
- [ ] Check server logs for emoji markers

## 🚀 Expected Behavior After Fix

1. **API Test Endpoint:** 
   - URL: `/api/test.php`
   - Returns: JSON response
   - Status: 200 OK

2. **Track Page Filters:**
   - Click "Search Tracking Numbers"
   - Results load without error
   - Console shows successful API call

3. **Server Logs:**
   - Shows emoji-marked log entries
   - No PHP errors
   - All requests processed successfully

## 📞 If Still Not Working

1. **Test API directly:**
   ```bash
   curl -X POST https://www.tukarkuy.web.id/api/search \
     -H "Content-Type: application/json" \
     -H "Cookie: PHPSESSID=your_session_id" \
     -d '{"dest_country":"US"}'
   ```

2. **Check .htaccess syntax:**
   ```bash
   apachectl -t
   # or
   apache2ctl -t
   ```

3. **Verify mod_rewrite enabled:**
   ```bash
   apache2ctl -M | grep rewrite
   # Should show: rewrite_module (shared)
   ```

4. **Upload diagnostic info:**
   - Screenshot of browser console
   - Copy server error logs
   - Result of `/api/test.php` access

---

**Last Updated:** July 5, 2026
**Status:** Fixed - Waiting for deployment test
