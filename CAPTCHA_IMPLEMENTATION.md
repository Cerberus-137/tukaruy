# Cloudflare Turnstile CAPTCHA Implementation

## Overview
Implemented Cloudflare Turnstile CAPTCHA on login and register pages to prevent bot attacks and unauthorized account creation.

## Configuration

### Credentials
- **Site Key**: `0x4AAAAAADv5iD6IFqguAWUU`
- **Secret Key**: `0x4AAAAAADv5iGUdF-BTe-Rgo6BLfApsm4Q`

### Files Modified

#### 1. `login.php`
- Added Turnstile script tag in head: `https://challenges.cloudflare.com/turnstile/v0/api.js`
- Added `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY` constants
- Implemented `verifyCaptcha()` function to validate CAPTCHA tokens
- Added CAPTCHA token validation before login attempt
- Replaced placeholder with actual Turnstile widget: `<div class="cf-turnstile" data-sitekey="..." data-theme="dark"></div>`

#### 2. `register.php`
- Added Turnstile script tag in head: `https://challenges.cloudflare.com/turnstile/v0/api.js`
- Added `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY` constants
- Implemented `verifyCaptcha()` function to validate CAPTCHA tokens
- Added CAPTCHA token validation before registration attempt
- Replaced placeholder with actual Turnstile widget: `<div class="cf-turnstile" data-sitekey="..." data-theme="dark"></div>`

## How It Works

### Frontend
1. Turnstile script loads from Cloudflare CDN
2. When form loads, CAPTCHA widget renders with dark theme
3. User solves CAPTCHA challenge
4. Cloudflare injects `cf-turnstile-response` token into hidden form field
5. Form submission includes the token

### Backend
1. Server receives POST request with `cf-turnstile-response` token
2. Token is verified against Cloudflare API using secret key
3. Cloudflare returns `success: true` if valid
4. If valid, proceed with login/register logic
5. If invalid, show error: "CAPTCHA verification failed"

### Verification API
- **Endpoint**: `https://challenges.cloudflare.com/turnstile/v0/siteverify`
- **Method**: POST
- **Parameters**: 
  - `secret`: Secret key for verification
  - `response`: CAPTCHA token from client

## Error Handling

### CAPTCHA Error Cases
1. **Empty token**: "Please complete the CAPTCHA"
2. **Verification failed**: "CAPTCHA verification failed. Please try again."
3. **API unreachable**: Logs error and shows verification failed message

### Form Validation Errors (still applied)
- Empty email/password/name fields
- Invalid email format
- Password too short (< 8 characters)

**Validation Order:**
1. Check form fields are filled
2. Check CAPTCHA is completed
3. For register: Check password length and email format
4. Verify CAPTCHA with Cloudflare
5. Proceed with login/register

## Security Features

✅ **Bot Prevention**: Turnstile blocks automated attacks
✅ **DDoS Protection**: Cloudflare provides additional DDoS protection
✅ **Token Validation**: Server-side verification of tokens
✅ **Error Logging**: All verification failures logged for debugging
✅ **Timeout Protection**: 10-second timeout on API requests
✅ **Theme**: Dark theme matches website design

## Testing Checklist

- [ ] Login page shows CAPTCHA widget
- [ ] Register page shows CAPTCHA widget
- [ ] Can complete CAPTCHA and submit login form
- [ ] Can complete CAPTCHA and submit register form
- [ ] Without completing CAPTCHA, form shows error
- [ ] Submitting without checking CAPTCHA shows "Please complete the CAPTCHA"
- [ ] Browser console has no JavaScript errors
- [ ] CAPTCHA widget is responsive on mobile
- [ ] Dark theme CAPTCHA is visible and readable

## Troubleshooting

### CAPTCHA not appearing
- Verify script is loaded: Check browser console for Turnstile script
- Check site key is correct
- Verify domain is allowed in Cloudflare dashboard

### "CAPTCHA verification failed" error
- Check secret key is correct
- Verify Cloudflare API is accessible (check internet connection)
- Check server time is synced (tokens have expiry)
- Look in error logs for specific failure reason

### Token not being submitted
- Verify form has `method="POST"`
- Check Turnstile script is fully loaded
- Look for JavaScript errors in browser console

## Future Enhancements

- Consider implementing for other forms (forgot password, etc.)
- Add rate limiting per IP address
- Add account lockout after failed attempts
- Implement custom error messages
- Add CAPTCHA bypass for trusted users (optional)

## Cloudflare Dashboard
To manage CAPTCHA settings:
1. Go to https://dash.cloudflare.com
2. Navigate to CAPTCHA > Turnstile
3. Verify sites and keys are configured correctly
4. Review analytics and block rates

## API Response Example

**Success Response:**
```json
{
  "success": true,
  "challenge_ts": "2026-07-05T10:30:00Z",
  "hostname": "tukaruy.online",
  "error_codes": []
}
```

**Failure Response:**
```json
{
  "success": false,
  "challenge_ts": "2026-07-05T10:30:00Z",
  "hostname": "tukaruy.online",
  "error_codes": ["invalid-token"]
}
```

