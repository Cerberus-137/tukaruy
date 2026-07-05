# Cloudflare Turnstile CAPTCHA - Setup Guide

## Problem
Cloudflare Turnstile CAPTCHA widget showing: **"Unable to connect to website"**

## Root Cause
The site key `0x4AAAAAADv5iD6IFqguAWUU` is not registered for your current domain in Cloudflare Turnstile dashboard.

### Why This Happens
1. **Domain Mismatch**: CAPTCHA site keys are tied to specific domains
2. **Domain Not Registered**: Your domain (e.g., `tukaruy.online`) hasn't been added to Cloudflare Turnstile
3. **Localhost/Wrong Domain**: If testing on `localhost`, the site key won't work (site keys only for production domains)

---

## Solution: Setup Cloudflare Turnstile for Your Domain

### Step 1: Go to Cloudflare Dashboard
1. Visit: https://dash.cloudflare.com
2. Login with your Cloudflare account
3. Select your domain (if using tukaruy.online)

### Step 2: Navigate to Turnstile
1. Go to **"Turnstile"** or **"CAPTCHA"** section
2. If not visible, search for "Turnstile" in the dashboard

### Step 3: Create New Site
1. Click **"Create new site"** or **"Add site"**
2. Enter your domain: `tukaruy.online` (or your domain)
3. Select **"Managed Challenge"** mode
4. Customize appearance if needed
5. Click **"Create"**

### Step 4: Get New Keys
Cloudflare will show you:
- **Site Key**: (publicly visible in HTML)
- **Secret Key**: (kept secret on server)

### Step 5: Update Your Code

Replace the keys in `login.php` and `register.php`:

**File: `login.php` (Line 6-7)**
```php
define('TURNSTILE_SITE_KEY', 'YOUR_NEW_SITE_KEY_HERE');
define('TURNSTILE_SECRET_KEY', 'YOUR_NEW_SECRET_KEY_HERE');
```

**File: `register.php` (Line 6-7)**
```php
define('TURNSTILE_SITE_KEY', 'YOUR_NEW_SITE_KEY_HERE');
define('TURNSTILE_SECRET_KEY', 'YOUR_NEW_SECRET_KEY_HERE');
```

### Step 6: Test
1. Clear browser cache (Ctrl+Shift+Delete)
2. Visit `/login` or `/register`
3. CAPTCHA widget should now load ✅

---

## Temporary Fix (If Keys Not Yet Available)

If you don't have valid Cloudflare Turnstile keys yet, the code now gracefully falls back:

- ✅ CAPTCHA widget still shows (if script loads)
- ✅ Login/Register works even if CAPTCHA fails to load
- ✅ If CAPTCHA loads and user completes it, verification is enforced
- ✅ If CAPTCHA fails to load, user can still login/register normally

**This is a SECURITY FALLBACK - not recommended for production**

To enforce CAPTCHA (require valid token), change line in `login.php`:
```php
// Change this line:
// $captchaValid = false;

// To:
$captchaValid = false;  // Uncomment to require CAPTCHA
```

---

## Testing Locally (Development)

If you want to test CAPTCHA locally on `localhost`:

### Option 1: Use Localhost Keys
Cloudflare provides test keys for localhost:
- **Site Key**: `1x00000000000000000000AA`
- **Secret Key**: `1x0000000000000000000000ffffffffffffffff`

Update `login.php` and `register.php`:
```php
define('TURNSTILE_SITE_KEY', '1x00000000000000000000AA');
define('TURNSTILE_SECRET_KEY', '1x0000000000000000000000ffffffffffffffff');
```

### Option 2: Skip CAPTCHA for Development
Comment out the CAPTCHA HTML in `login.php` and `register.php`:
```html
<!-- Temporarily disable for testing -->
<!-- <div class="cf-turnstile" data-sitekey="..." data-theme="dark"></div> -->
```

---

## Current Implementation Details

### Files Updated
- `login.php` - Graceful CAPTCHA fallback
- `register.php` - Graceful CAPTCHA fallback
- Both files have error logging if CAPTCHA fails to load

### Security Levels

**Level 1: No CAPTCHA** (Current - Temporary)
- Widget shows if it loads
- Form works even if CAPTCHA fails
- ⚠️ Low security - use only for testing

**Level 2: Optional CAPTCHA** (If widget loads)
- Widget required if it loads successfully
- Form works if widget fails to load
- ⚠️ Medium security

**Level 3: Required CAPTCHA** (Production)
- Widget MUST load and be completed
- Form blocks if widget fails
- ✅ High security

### Enable Level 3 (Production)

Edit both `login.php` and `register.php`, find this section:

```php
} else {
    // Log warning but allow bypass if CAPTCHA fails to load
    error_log('Warning: CAPTCHA token not received - possible widget load failure');
    // Set to false if you want to enforce CAPTCHA
    // $captchaValid = false;
}
```

Change to:
```php
} else {
    $error = 'Please complete the CAPTCHA';
    // Enforce CAPTCHA requirement
}
```

---

## Troubleshooting

### "Unable to connect to website" Error

**Cause 1: Domain not registered**
- ✅ Solution: Add your domain to Cloudflare Turnstile dashboard

**Cause 2: Wrong site key**
- ✅ Solution: Copy the EXACT site key from Cloudflare dashboard

**Cause 3: Domain mismatched**
- ✅ Solution: If using `localhost`, use localhost test keys

**Cause 4: Script not loading**
- Check browser console for errors
- Check if `https://challenges.cloudflare.com/turnstile/v0/api.js` loads
- Allow network access to Cloudflare CDN

### CAPTCHA Widget Not Appearing

1. Check browser console (F12 → Console)
2. Look for errors related to Turnstile
3. Verify site key is correct:
   ```html
   <div class="cf-turnstile" data-sitekey="YOUR_SITE_KEY"></div>
   ```
4. Check if script tag is present in `<head>`:
   ```html
   <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
   ```

### Form Submits Without CAPTCHA

**This is expected with current setup** (graceful fallback).

To require CAPTCHA:
1. Get valid site and secret keys from Cloudflare
2. Update keys in `login.php` and `register.php`
3. Uncomment the enforcement line: `$captchaValid = false;`

---

## Production Checklist

- [ ] Registered domain in Cloudflare Turnstile
- [ ] Copied Site Key and Secret Key
- [ ] Updated keys in `login.php` (line 6-7)
- [ ] Updated keys in `register.php` (line 6-7)
- [ ] Tested CAPTCHA widget loads
- [ ] Tested CAPTCHA verification works
- [ ] Enabled CAPTCHA enforcement (Level 3)
- [ ] Verified error logging works
- [ ] Tested with multiple browsers

---

## Monitoring & Analytics

In Cloudflare Dashboard, you can view:
- CAPTCHA completion rate
- Blocked bot attempts
- Geographic distribution
- Challenge difficulty metrics

---

## Alternative Solutions

If Cloudflare Turnstile doesn't work, alternatives:

1. **reCAPTCHA v3** (Google)
2. **hCaptcha** (Privacy-focused)
3. **Simple Math CAPTCHA** (Custom)
4. **Email Verification** (Instead of CAPTCHA)

---

## Quick Reference

### Current Status
- ✅ CAPTCHA code implemented
- ⚠️ Widget fails to load (domain not registered)
- ✅ Graceful fallback working
- ⚠️ Security: Low (fallback mode)

### To Fix
1. Register domain in Cloudflare Turnstile
2. Get new Site Key and Secret Key
3. Update in login.php and register.php
4. Test and enable enforcement

### Estimated Setup Time
- 5-10 minutes if you have Cloudflare account
- 10-15 minutes if need to create account
- 1-2 minutes to update keys

---

## Support Links

- **Cloudflare Turnstile Docs**: https://developers.cloudflare.com/turnstile/
- **Setup Guide**: https://developers.cloudflare.com/turnstile/get-started/
- **Troubleshooting**: https://developers.cloudflare.com/turnstile/troubleshooting/

