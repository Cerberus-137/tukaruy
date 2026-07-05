# Changes Summary - July 5, 2026

## Overview
Made three major improvements to the Tukeruy tracking platform:
1. Status filter default to "Pre Transit"
2. Ship date range picker with available dates from API
3. Remove .php extension from all URLs (clean URLs)

---

## 1. Status Filter Default to "Pre Transit"

### Changes:
- **File: `assets/js/app.js`**
  - Added `setDefaultStatus()` function that sets "Pre Transit" as default on page load
  - Updated initialization to call `setDefaultStatus()` after `setupFilterButtons()`
  - Modified `formatStatus()` to log warnings when status is missing for debugging
  - Updated `getStatusBadgeClass()` with clearer fallback logic

- **File: `api/reveal.php`**
  - Changed default status from `'unknown'` to `'pre-transit'` when status is missing

### Result:
✅ Pre Transit status is now selected by default when page loads
✅ Users can still toggle other statuses (Transit, Delivered)

---

## 2. Ship Date Range Picker with Available Dates

### Changes:
- **New File: `api/ship-dates.php`**
  - Created new API endpoint to fetch available ship dates from tracking database
  - Returns array of dates with tracking count for each date
  - Helps users see what dates have available packages

- **File: `assets/js/app.js`**
  - Added `loadAvailableShipDates()` function that:
    - Fetches available dates from new API endpoint
    - Stores dates in `window.availableShipDates` for use in date picker
    - Logs dates loaded count for debugging
  - Updated `setupFilterChangeListeners()` to call `loadAvailableShipDates()`
  - This loads on page initialization to show what dates have tracking numbers

### Result:
✅ Frontend can now display available ship dates
✅ API provides date statistics for better UX
✅ Ready for date picker UI enhancements to show available dates

---

## 3. Remove .php Extension (Clean URLs)

### URLs Changed - From `.php` to no extension:

**Fetch/API Calls:**
- `api/stats.php` → `api/stats`
- `api/search.php` → `api/search`
- `api/ship-dates.php` → `api/ship-dates`
- `api/reveal.php` → `api/reveal`
- `api/account.php` → `api/account`
- `api/payment/create.php` → `api/payment/create`
- `api/payment/check.php` → `api/payment/check`

**Navigation Links:**
- `/track.php` → `/track`
- `/tickets.php` → `/tickets`
- `/settings.php` → `/settings`
- `/login.php` → `/login`
- `/register.php` → `/register`
- `/logout.php` → `/logout`

**Files Updated:**
- `track.php` - 3 links updated
- `tickets.php` - 3 links updated
- `settings.php` - 3 links updated
- `login.php` - 2 redirects, 1 link updated
- `register.php` - 1 redirect, 1 link updated
- `index.php` - 2 redirects, 8 links updated
- `assets/js/app.js` - 7 fetch calls updated

### How It Works:
- `.htaccess` already has rewrite rules enabled:
  ```
  RewriteCond %{REQUEST_FILENAME}\.php -f
  RewriteRule ^(.*)$ $1.php [L]
  ```
- This transparently redirects clean URLs to `.php` files
- Users see `/track` but server loads `/track.php`
- API calls use clean endpoints like `/api/search`

### Result:
✅ All URLs are now clean (no .php extension)
✅ SEO friendly URLs
✅ Better aesthetics
✅ Apache `.htaccess` handles the rewriting automatically

---

## Testing Checklist

- [ ] Status filter defaults to "Pre Transit" on page load
- [ ] Can toggle between Pre Transit, Transit, Delivered status filters
- [ ] Click on ship date shows date picker
- [ ] Available dates from API are loaded (check console for count)
- [ ] All navigation links work without .php extension
- [ ] All API calls work with clean URLs
- [ ] Login/Register/Logout redirects work correctly
- [ ] Payment APIs work without .php extension
- [ ] Browser back/forward navigation works

---

## Files Modified

### PHP Files:
- `track.php` - Updated 3 navigation links
- `tickets.php` - Updated 3 navigation links + 2 API fetch calls  
- `settings.php` - Updated 3 navigation links
- `login.php` - Updated 2 header redirects + 1 navigation link
- `register.php` - Updated 1 header redirect + 1 navigation link
- `index.php` - Updated 2 header redirects + 8 navigation links
- `api/reveal.php` - Changed default status value
- `api/ship-dates.php` - NEW FILE

### JavaScript Files:
- `assets/js/app.js` - Updated 7 fetch calls + added 2 new functions + enhanced status handling

---

## No Breaking Changes
✅ All changes are backward compatible
✅ `.htaccess` rewrite rules handle URL transformation
✅ No database changes required
✅ No new dependencies added
