# ✅ FINAL CHECK - API Working, Need To Check Server Logs

## 🎉 GOOD NEWS!

**`/api/test.php` berhasil return JSON!** Ini artinya `.htaccess` sudah BENAR dan API endpoint bisa diakses.

```json
{
    "success": true,
    "message": "API endpoint is working",
    "timestamp": "2026-07-05 16:24:59"
}
```

---

## 🔍 NEXT STEP: Check Server Logs

Sekarang kita perlu lihat **server error log** untuk tahu kenapa `/api/search` masih error.

### Cara 1: Via cPanel / Hosting Panel
1. Login ke cPanel
2. Go to "Error Log" atau "Logs"
3. Cari log dengan emoji: 🚨, 🔍, ✅, ❌
4. Copy paste semua log yang muncul setelah kamu klik "Search Tracking Numbers"

### Cara 2: Via SSH
```bash
# Tail error log
tail -f /var/log/apache2/error.log
# atau
tail -f /var/log/httpd/error_log

# Lalu akses track page dan klik search
# Log akan muncul realtime
```

### Cara 3: PHP Error Log
```bash
# Check PHP error log location
php -i | grep error_log

# Then tail it
tail -f /path/to/php_error.log
```

---

## 📋 Yang Harus Dicari Di Log

Setelah klik "Search Tracking Numbers" di track page, cari log ini:

### ✅ Log SUCCESS (Harusnya muncul):
```
=================================================
🚨 API SEARCH.PHP EXECUTED!
🚨 THIS FILE IS BEING CALLED!
=================================================
🔍 Search API Request - Method: POST, URI: /api/search
📥 Raw input: {"dest_country":"US",...}
📋 Search API: Filters received - {...}
✅ TukeruyAPI initialized
🎯 Carrier filter: [...]
🎯 Status filter: [...]
🔎 Search API: Processing filters - {...}
🌐 Calling TrackTaco API...
✅ TrackTaco API responded
✅ Search API: Found 10 results
📤 Sending response: 1234 bytes
```

### ❌ Log ERROR (Kalau ada masalah):
```
❌ Search API Error: [pesan error]
❌ Error type: Exception
❌ Stack trace: [detail error]
```

---

## 🐛 Common Errors & Meaning

### Error 1: "Invalid API key"
**Meaning:** TrackerTaco API key salah atau tidak valid

**Fix:**
1. Check `config.php` - API key: `tt_live_T5w7d...`
2. Atau set di database admin settings
3. Verify API key di https://app.tracktaco.com

### Error 2: "insufficient_credits"
**Meaning:** Account TrackerTaco habis credit

**Fix:**
1. Top up credit di https://app.tracktaco.com/app/account/billing
2. Check balance via API: `curl https://v2.tracktaco.com/v2/account -H "Authorization: Bearer YOUR_API_KEY"`

### Error 3: "rate_limited"
**Meaning:** Terlalu banyak request dalam waktu singkat

**Fix:**
1. Tunggu beberapa detik
2. Cek rate limit: 5 req/sec sustained, 20 req/10sec burst

### Error 4: "Call to undefined function curl_init"
**Meaning:** cURL extension tidak enabled

**Fix:**
```bash
# Ubuntu/Debian
sudo apt-get install php-curl
sudo service apache2 restart

# Check if enabled
php -m | grep curl
```

### Error 5: "Failed to open stream: HTTP request failed"
**Meaning:** Server tidak bisa connect ke TrackerTaco API

**Fix:**
1. Check firewall allow outbound HTTPS
2. Check DNS resolve: `nslookup v2.tracktaco.com`
3. Test connection: `curl https://v2.tracktaco.com/v2/account -H "Authorization: Bearer YOUR_KEY"`

---

## 🧪 Manual Test API

Test TrackerTaco API directly dari command line:

```bash
# Get account info
curl https://v2.tracktaco.com/v2/account \
  -H "Authorization: Bearer tt_live_T5w7dupesqnPFQprpV6ozAdE40LKird_BZkrF4TL7dk"

# Search tracking numbers
curl https://v2.tracktaco.com/v2/tns/search \
  -X POST \
  -H "Authorization: Bearer tt_live_T5w7dupesqnPFQprpV6ozAdE40LKird_BZkrF4TL7dk" \
  -H "Content-Type: application/json" \
  -d '{
    "searches": [{
      "filter": {
        "dest": { "country": "US" },
        "status": ["pre-transit"]
      },
      "page_size": 10
    }]
  }'
```

**Expected Response:**
```json
{
  "searches": [{
    "results": [...],
    "next_cursor": "...",
    "total": 42
  }]
}
```

**If Error:**
```json
{
  "error": {
    "code": "insufficient_credits",
    "message": "Your balance is 0...",
    "doc_url": "..."
  }
}
```

---

## 📞 WHAT TO DO NOW

1. **Upload file yang sudah diperbaiki:**
   - ✅ `api/search.php` (enhanced logging)

2. **Test track page:**
   - Go to https://www.tukarkuy.web.id/track
   - Click "Search Tracking Numbers"
   - Check browser console

3. **Check server logs:**
   - Look for emoji markers (🚨, 🔍, ✅, ❌)
   - Copy ALL logs that appear
   - Send to me

4. **Test manual API:**
   - Run curl command above
   - See if TrackerTaco API responds

5. **Report back:**
   - Screenshot browser console
   - Copy server error log
   - Result dari manual curl test

---

## 💡 LIKELY ISSUES

Based on typical scenarios:

### Scenario 1: API Key Invalid
- Log shows: "❌ Invalid API key"
- Fix: Update API key in config.php or database

### Scenario 2: No Credits
- Log shows: "❌ insufficient_credits"
- Fix: Top up credit di TrackerTaco

### Scenario 3: Network Issue
- Log shows: "❌ Failed to connect"
- Fix: Check firewall, DNS, connectivity

### Scenario 4: Response Format Issue
- Log shows: "⚠️ Unexpected API response structure"
- Fix: Check TrackerTaco API version

---

**NEXT ACTION:**
Upload `api/search.php` yang baru, test, dan **SHARE SERVER ERROR LOG** dengan saya!

Server log adalah kunci untuk tahu error yang sebenarnya. Tanpa log, saya hanya bisa tebak-tebak.

---

Last Updated: July 5, 2026, 4:30 PM
Status: Waiting for server log analysis
