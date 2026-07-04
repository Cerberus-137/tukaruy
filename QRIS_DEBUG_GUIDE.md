# QRIS Payment Debug Guide

## Current Status

The QRIS payment system creates payment records successfully in the database, but the QR code image is not displaying. This guide will help you debug the issue.

## Checking Browser Console

1. Open the payment page (tickets.php)
2. Open browser Developer Tools (F12)
3. Go to Console tab
4. Try to create a QRIS payment
5. Look for these log messages:
   - `Payment response:` - Shows the full API response
   - `showPaymentModal called with qris:` - Shows what data is passed to the modal

## Expected QRIS Response Format

The QRIS API should return:

```json
{
  "success": true,
  "payment_method": "qrispay",
  "qris": {
    "qris_id": "some-qris-id",
    "qris_image_url": "https://api.qrispy.id/qr/image/xxx",
    "amount": 450000,
    "expired_at": "2026-07-04 19:55:00",
    "expires_in_seconds": 900,
    "payment_reference": "TKY-3-1783165239"
  }
}
```

## Common Issues

### Issue 1: qris_image_url is empty or null

**Symptoms:** Payment created in database but QR code not showing.

**Cause:** The QRIS Pay API endpoint may:
- Be returning a different field name (e.g., `qr_url`, `qr_image`, `qr_code_url`)
- Not be generating the QR code at all
- Require additional parameters

**Check server logs:**
```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/php-error.log
```

Look for these log entries:
- `QRIS API Request:`
- `QRIS API Response Code:`
- `QRIS API Response Body:`
- `QRIS Generate Full Response:`
- `QRIS Response missing qris_image_url`

### Issue 2: API Authentication Failed

**Cause:** The QRIS Pay API token may be invalid or expired.

**Solution:** 
1. Contact QRIS Pay support to verify your API token
2. Update the token in database: `admin_settings` table, key `qrispay_api_token`
3. Or update in `config.php` function `getQRISPayAPIToken()`

### Issue 3: Wrong API Endpoint

**Current endpoint:** `https://api.qrispy.id/api/payment/qris/generate`

**Verify with QRIS Pay documentation:**
- Ensure the endpoint URL is correct
- Check if it should be `/generate` or `/create` or something else
- Update in `config.php` constant `QRISPAY_API_URL`

## Testing the API Directly

Create a test file `test_qris.php`:

```php
<?php
require_once 'config.php';
require_once 'api/QRISPayAPI.php';

$qris = new QRISPayAPI();

try {
    $result = $qris->generateQRIS(50000, 'TEST-' . time(), 'https://tukarkuy.web.id');
    echo "Success!\n";
    echo "Response:\n";
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

Run: `php test_qris.php`

## QRIS Pay API Documentation Reference

If you have access to QRIS Pay API documentation, verify:

1. **Authentication method:**
   - Current: `X-API-Token: xxx` header
   - Should it be `Authorization: Bearer xxx`?

2. **Request format:**
   - Current: POST with JSON body `{amount: 50000, payment_reference: "xxx", return_url: "xxx"}`
   - Check if field names are correct

3. **Response format:**
   - Current code expects: `qris_id`, `qris_image_url`, `expired_at`, etc.
   - Check actual field names in documentation

## Saweria Payment Issue

**Current Error:** "API request failed"

**JWT Token Status:** Valid (expires July 7, 2026 at 18:52 GMT+7)

**Cause:** The Saweria API integration is trying to call an endpoint that might not exist or requires different parameters.

**Check server logs for:**
- `Saweria API Request:`
- `Saweria API Response Code:`
- `Saweria API Response:`

**Common fixes:**
1. Verify Saweria API endpoint URLs
2. The `/stream` endpoint might be `/profile` or `/user`
3. The token might need refresh even if not expired

## Next Steps

1. Check browser console logs
2. Check server error logs
3. Test the QRIS API with the test script above
4. Contact QRIS Pay support for API documentation
5. Verify API endpoints and field names

## Contact

If issues persist:
1. Provide browser console logs
2. Provide server error logs (from the time of payment attempt)
3. Share any QRIS Pay API documentation you have
