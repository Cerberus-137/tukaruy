# Troubleshooting Guide

## Common Issues & Solutions

### 1. Payment Failed: "QRIS generated successfully"

**Symptom:** Payment modal shows error even though QRIS is generated

**Cause:** Response structure mismatch or missing data validation

**Solution:**
1. Check browser console for detailed error
2. Verify QRISPay API token in database:
```sql
SELECT * FROM admin_settings WHERE setting_key = 'qrispay_api_token';
```

3. Check error logs:
```bash
tail -f /var/log/apache2/tukeruy_error.log
```

4. Test QRISPay API directly:
```bash
curl -X POST https://api.qrispy.id/api/payment/qris/generate \
  -H "X-API-Token: YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount": 50000}'
```

**Fixed in:** v1.1 - Added proper response validation and error logging

---

### 2. Track.php Page is Very Slow (Lag)

**Symptom:** Page takes 5-10 seconds to load

**Cause:** API calls to TrackTaco during page load blocking render

**Solution Implemented:**
- ✅ Removed synchronous API calls from PHP
- ✅ Load stats asynchronously via JavaScript
- ✅ Implemented stats caching (5 minutes)
- ✅ Stats display shows loading state

**Performance:**
- Before: 5-10 seconds page load
- After: < 1 second page load

**Cache Location:** `/tmp/tukeruy_stats_cache.json`

**Clear Cache:**
```bash
rm /tmp/tukeruy_stats_cache.json
```

---

### 3. QRIS Code Not Displaying

**Possible Causes:**
1. Invalid API token
2. Network connectivity
3. QRISPay API down

**Debug Steps:**

1. **Check API Token:**
```sql
SELECT setting_value FROM admin_settings WHERE setting_key = 'qrispay_api_token';
```

2. **Test API Connection:**
```bash
curl -H "X-API-Token: YOUR_TOKEN" \
  https://api.qrispy.id/api/payment/balance
```

3. **Check Browser Console:**
- Open DevTools (F12)
- Check Network tab for failed requests
- Look for error messages in Console tab

4. **Check PHP Error Log:**
```bash
tail -f /var/log/apache2/tukeruy_error.log
```

---

### 4. Payment Status Not Updating

**Symptom:** Payment made but credits not added

**Cause:** Webhook not triggered or status check failing

**Manual Check:**
```sql
-- Check payment status
SELECT * FROM payments WHERE user_id = YOUR_USER_ID ORDER BY created_at DESC;

-- Manually update if needed
UPDATE payments SET status = 'paid', paid_at = NOW() WHERE qris_id = 'YOUR_QRIS_ID';
UPDATE users SET tickets = tickets + AMOUNT WHERE id = YOUR_USER_ID;
```

**Prevention:**
- Payment check runs every 3 seconds
- Maximum 15 minutes (300 checks)
- Status: pending → paid

---

### 5. Database Connection Failed

**Symptom:** "Database connection failed" error

**Solutions:**

1. **Check MariaDB Status:**
```bash
systemctl status mariadb
```

2. **Restart MariaDB:**
```bash
systemctl restart mariadb
```

3. **Verify Credentials:**
```bash
mysql -u tukeruy_user -p tukeruy
```

4. **Check config.php:**
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tukeruy');
define('DB_USER', 'tukeruy_user');
define('DB_PASS', 'YOUR_PASSWORD');
```

---

### 6. TrackTaco API Errors

**Common Errors:**

**"Invalid API Key"**
```sql
-- Update API key
UPDATE admin_settings 
SET setting_value = 'tt_live_YOUR_NEW_KEY' 
WHERE setting_key = 'tracktaco_api_key';
```

**"Rate Limited"**
- Wait 1 minute
- Reduce search frequency
- Check rate limits in TrackTaco dashboard

**"Insufficient Credits"**
- Admin sees TrackTaco credits
- Users see Tukeruy tickets
- Check: https://tracktaco.com/dashboard

---

### 7. Search Returns No Results

**Possible Causes:**
1. Too restrictive filters
2. API rate limit
3. No data for selected criteria

**Debug:**

1. **Test with minimal filters:**
- Remove all filters
- Try single country (US)
- Try single carrier (FedEx)

2. **Check API Response:**
```javascript
// Open browser console
fetch('api/search.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        filters: {dest_country: 'US'},
        page_size: 10
    })
})
.then(r => r.json())
.then(console.log);
```

---

### 8. Stats Showing "0" or Loading Forever

**Cause:** Stats API failing or cache issue

**Solutions:**

1. **Clear Cache:**
```bash
rm /tmp/tukeruy_stats_cache.json
```

2. **Check Stats API:**
```bash
curl http://localhost/api/stats.php
```

3. **Manual Stats Update:**
Create background job:
```bash
# /etc/cron.d/tukeruy-stats
*/5 * * * * www-data curl -s http://localhost/api/stats.php > /dev/null
```

---

### 9. Session Expired / Logged Out Unexpectedly

**Causes:**
- Session timeout (24 hours default)
- Server restart
- Cache cleared

**Solutions:**

1. **Increase Session Lifetime:**
```php
// config.php
session_set_cookie_params([
    'lifetime' => 86400 * 7, // 7 days
    // ... other params
]);
```

2. **Check Session Path:**
```bash
ls -la /var/lib/php/sessions/
```

---

### 10. Admin Panel Not Accessible

**Symptom:** 403 Forbidden or redirect loop

**Cause:** Permission check failing

**Solution:**
```sql
-- Check user role
SELECT id, email, role FROM users WHERE email = 'admin@tukaruy.online';

-- Update to admin if needed
UPDATE users SET role = 'admin' WHERE email = 'admin@tukaruy.online';
```

---

## Performance Optimization

### 1. Enable PHP OpCache

```bash
nano /etc/php/7.4/apache2/php.ini
```

Add:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

Restart:
```bash
systemctl restart apache2
```

### 2. Database Indexing

Already implemented:
- `users.email` - Index
- `payments.user_id` - Index
- `payments.qris_id` - Index
- `payments.status` - Index

### 3. Enable Gzip Compression

```bash
nano /etc/apache2/mods-available/deflate.conf
```

Add:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

Enable:
```bash
a2enmod deflate
systemctl restart apache2
```

---

## Monitoring

### Check System Resources

```bash
# Disk space
df -h

# Memory usage
free -m

# Apache status
systemctl status apache2

# MariaDB status
systemctl status mariadb

# Active connections
netstat -an | grep :80 | wc -l
```

### Monitor Error Logs

```bash
# Apache errors
tail -f /var/log/apache2/tukeruy_error.log

# MariaDB errors
tail -f /var/log/mysql/error.log

# System logs
tail -f /var/log/syslog
```

### Monitor Database

```sql
-- Active connections
SHOW PROCESSLIST;

-- Table sizes
SELECT 
    table_name, 
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'tukeruy'
ORDER BY (data_length + index_length) DESC;

-- Payment statistics
SELECT 
    status,
    COUNT(*) as count,
    SUM(amount) as total_amount,
    SUM(tickets) as total_tickets
FROM payments
GROUP BY status;
```

---

## Getting Help

1. **Check Logs First:**
   - Apache: `/var/log/apache2/tukeruy_error.log`
   - Browser Console (F12)
   - Network Tab for API errors

2. **Collect Debug Info:**
   - Error message (exact text)
   - Steps to reproduce
   - Browser & OS version
   - Server error logs

3. **Common Solutions:**
   - Clear browser cache
   - Clear server cache
   - Restart Apache
   - Check API keys
   - Verify database connection

4. **Still Need Help:**
   - Document the issue
   - Include error logs
   - Describe expected vs actual behavior
   - Contact system administrator

---

**Last Updated:** January 2026
