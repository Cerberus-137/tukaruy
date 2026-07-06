# API Routing Issues - Complete Fix Guide

## 🔧 FIXES APPLIED

### 1. **Root .htaccess - Proper API Route Handling**

**File:** `.htaccess`

**Changes:**
- Changed from using `RewriteCond` (conditions) to `RewriteRule` with pass-through (`-`) for API endpoints
- This is more reliable and prevents conflicts with nested rewrites

```apache
# Skip ALL rewriting for API endpoints (they need .php extension)
RewriteRule ^(api|admin/api)($|/) - [L]

# Remove .php extension for non-API files
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteRule ^(.*)$ $1.php [L]
```

**Why this works:**
- `RewriteRule ^(api|admin/api)($|/)` matches both `/api/` and `/admin/api/`
- `-` means "pass through unchanged" - don't modify the request
- `[L]` means "last rule" - stop processing further rules
- This ensures API requests reach the PHP files directly without `.php` removal attempts

### 2. **API Folder .htaccess - Clean URL Support**

**File:** `api/.htaccess`

**New Rules:**
```apache
RewriteEngine On

# Handle clean URLs (redirect /api/file to /api/file.php)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteRule ^([^.]+)$ $1.php [L]
```

**Purpose:**
- Allows requests like `/api/search` to automatically map to `/api/search.php`
- Supports both `/api/search.php` and `/api/search` clean URLs
- Only applies this rewrite if the target `.php` file exists

### 3. **Admin API Folder .htaccess - Clean URL Support**

**File:** `admin/api/.htaccess`

Same structure as `api/.htaccess` for consistency and clean URL support.

### 4. **Payment API Subdirectory**

**File:** `api/payment/.htaccess` (New)

Added `.htaccess` to handle nested path clean URLs:
```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME}\.php -f
RewriteRule ^([^.]+)$ $1.php [L]
```

### 5. **Standardized JSON Headers in All API Files**

**Updates made to:**
- `api/test.php`
- `api/account.php`
- `api/search.php`
- `api/ship-dates.php`
- `api/stats.php`
- `api/reveal.php`
- `api/payment/create.php`
- `api/payment/check.php`
- `admin/api/test-endpoint.php`
- `admin/api/packages.php`
- `admin/api/stats.php`
- `admin/api/settings.php`
- `admin/api/payment-methods.php`

**Changes:**
```php
// Before:
header('Content-Type: application/json');
echo json_encode($data);

// After:
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_SLASHES);
```

**Why:**
- `charset=utf-8` ensures proper character encoding
- `JSON_UNESCAPED_SLASHES` prevents unnecessary escaping of forward slashes in URLs/JSON

### 6. **Improved ship-dates.php Error Handling**

**File:** `api/ship-dates.php`

Added detailed logging to troubleshoot empty results:
```php
error_log('📅 Ship Dates API: Found ' . count($result['results']) . ' results');
error_log('📅 Ship Dates API: Response structure: ' . json_encode($result, JSON_UNESCAPED_SLASHES));
```

---

## ✅ TESTING PROCEDURES

### Test 1: Basic API Endpoint Accessibility

**Test direct .php access (should work):**
```bash
curl https://www.tukarkuy.web.id/api/test.php
```

**Expected Response:**
```json
{
  "success": true,
  "message": "API endpoint is working",
  "timestamp": "2026-07-05 12:34:56",
  "request_method": "GET",
  "request_uri": "/api/test.php",
  "php_sapi": "fpm-fcgi",
  "server_software": "nginx"
}
```

### Test 2: Clean URL API Endpoints

**Test clean URL without .php (should work after fixes):**
```bash
curl https://www.tukarkuy.web.id/api/test
```

**Expected:** Same JSON response as above (now via clean URL)

### Test 3: Admin API Endpoints

**Test admin API with clean URL:**
```bash
curl -H "Cookie: PHPSESSID=your_session_id" \
  https://www.tukarkuy.web.id/admin/api/packages
```

**Expected:** JSON response with packages list

### Test 4: Payment API Endpoints

**Test payment check with clean URL:**
```bash
curl "https://www.tukarkuy.web.id/api/payment/check?qris_id=TEST123" \
  -H "Cookie: PHPSESSID=your_session_id"
```

**Expected:** JSON response with payment status

### Test 5: Nested Path Clean URLs

**Test search API (POST):**
```bash
curl -X POST https://www.tukarkuy.web.id/api/search \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{"dest_country":"US"}'
```

**Expected:** JSON with search results

---

## 🔍 DEBUGGING CHECKLIST

### If You Get "Unexpected token '<'" Error

**Step 1: Verify .htaccess syntax**
```bash
# Check if .htaccess is valid (on server)
cat .htaccess

# Should see the new API routing rules:
# RewriteRule ^(api|admin/api)($|/) - [L]
```

**Step 2: Test direct .php access**
```bash
# This MUST return JSON
curl https://www.tukarkuy.web.id/api/test.php

# If this returns HTML, the .php file has an error
# Check server error logs for PHP errors
```

**Step 3: Check mod_rewrite is enabled**
```bash
# On server (if you have shell access)
apache2ctl -M | grep rewrite
# Should show: rewrite_module (shared)
```

**Step 4: Clear browser cache**
- Ctrl + Shift + R (Chrome/Firefox)
- Or test in Incognito/Private mode

### If API Returns Empty Data

**Check 1: Verify API logic**
- Run the PHP file directly in browser
- Check server error logs for PHP errors
- Look for emoji-marked log entries (📅, ❌, ✅)

**Check 2: For ship-dates.php specifically**
- Check if TukeruyAPI::search() is returning results
- Verify API response has `results` array
- Look for date parsing errors in logs

### If You Still Get 404 Errors

**Possible causes:**
1. File doesn't exist - check file paths
2. .htaccess not uploaded - verify file exists
3. Symbolic links issue - test with direct paths

**Test:**
```bash
# Verify files exist
ls -la api/test.php
ls -la api/payment/check.php
ls -la admin/api/packages.php
```

---

## 📋 FILES MODIFIED

### Configuration Files
- ✅ `.htaccess` - Root rewrite rules
- ✅ `api/.htaccess` - API folder rewrite rules
- ✅ `admin/api/.htaccess` - Admin API folder rewrite rules
- ✅ `api/payment/.htaccess` - NEW: Payment subdirectory rewrite rules

### API Endpoint Files (13 files updated with proper JSON headers)
- ✅ `api/test.php`
- ✅ `api/account.php`
- ✅ `api/search.php`
- ✅ `api/ship-dates.php` - Added enhanced logging
- ✅ `api/stats.php`
- ✅ `api/reveal.php`
- ✅ `api/payment/create.php`
- ✅ `api/payment/check.php`
- ✅ `admin/api/test-endpoint.php`
- ✅ `admin/api/packages.php`
- ✅ `admin/api/stats.php`
- ✅ `admin/api/settings.php`
- ✅ `admin/api/payment-methods.php`

---

## 🚀 DEPLOYMENT CHECKLIST

Before deploying to production:

- [ ] Upload `.htaccess` file
- [ ] Upload `api/.htaccess` file
- [ ] Upload `admin/api/.htaccess` file
- [ ] Upload `api/payment/.htaccess` file
- [ ] Update all 13 API endpoint files
- [ ] Clear browser cache
- [ ] Test `/api/test.php` endpoint
- [ ] Test `/api/test` clean URL
- [ ] Test `/api/search` endpoint (POST)
- [ ] Test `/api/ship-dates` endpoint
- [ ] Test `/api/payment/check` endpoint
- [ ] Test `/admin/api/packages` endpoint
- [ ] Check server error logs for PHP errors
- [ ] Check server access logs for 404 errors
- [ ] Verify all responses are JSON (no HTML)

---

## 📊 Expected Behavior After Fix

### API Routing Hierarchy

```
Request to /api/search
    ↓
Root .htaccess:
  - Check if request matches ^(api|admin/api)
  - YES → Pass through unchanged [L]
  - NO → Continue
    ↓
api/.htaccess:
  - Check if /api/search.php exists
  - YES → Rewrite to /api/search.php
    ↓
/api/search.php executes
    ↓
Returns: {
  "success": true,
  "results": [...],
  ...
} ← VALID JSON, Content-Type: application/json
```

### For Nested Paths

```
Request to /api/payment/check
    ↓
Root .htaccess:
  - Check if request matches ^(api|admin/api)
  - YES → Pass through unchanged [L]
    ↓
api/payment/.htaccess:
  - Check if /api/payment/check.php exists
  - YES → Rewrite to /api/payment/check.php
    ↓
/api/payment/check.php executes
    ↓
Returns valid JSON response
```

---

## 🔐 Security Notes

- ✅ All API files require authentication (session check)
- ✅ Admin APIs check for admin role
- ✅ All JSON responses include proper charset
- ✅ No HTML output can interfere with JSON parsing
- ✅ Error responses also return JSON format

---

## 📞 If Issues Persist

1. **Check the following in order:**
   - .htaccess file is uploaded correctly
   - All 13 API files are updated
   - Server error logs for PHP errors
   - Browser console for network errors

2. **Verify rewrite rules:**
   - Test with direct `.php` URLs first
   - Then test with clean URLs
   - Check server logs for rewrite activity

3. **Common mistakes:**
   - Forgetting to upload .htaccess files
   - Not updating all API files
   - Browser caching old responses
   - Server not having mod_rewrite enabled

4. **Get diagnostic info:**
   - Screenshot of browser console error
   - Server error log excerpt
   - Result of `/api/test.php` direct access
   - All .htaccess file contents

---

**Last Updated:** July 5, 2026  
**Status:** ✅ Complete - Ready for deployment testing
