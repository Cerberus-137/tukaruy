# Tukaruy ID System Guide

## Overview
Tukaruy menggunakan beberapa jenis ID untuk tracking dan transaksi. Dokumen ini menjelaskan setiap jenis ID dan kegunaannya.

---

## 1. Payment Reference ID

### Format
```
TKY-{user_id}-{timestamp}
```

### Contoh
```
TKY-1-1783164139
TKY-3-1783164567
```

### Kegunaan
- Identifikasi unik untuk setiap transaksi pembayaran
- Digunakan di QRIS dan Saweria
- Memudahkan tracking pembayaran
- Ditampilkan kepada user sebagai referensi

### Generate Code (PHP)
```php
$paymentRef = 'TKY-' . $user['id'] . '-' . time();
```

---

## 2. QRIS ID

### Format
UUID atau ID dari QRIS Pay API

### Contoh
```
ccf5aef4-0863-46dd-91d7-4f2b058da7eb
qris_123456789
```

### Kegunaan
- Identifikasi unik untuk setiap QRIS code
- Digunakan untuk check status pembayaran
- Disimpan di database untuk tracking
- Diperlukan untuk polling payment status

### Query Payment Status
```php
// Check QRIS payment status
$qrisPay = new QRISPayAPI();
$status = $qrisPay->checkPaymentStatus($qrisId);
```

### Database Storage
```sql
INSERT INTO payments (
    user_id, 
    payment_method, 
    qris_id,  -- UUID dari QRIS
    amount, 
    tickets, 
    status, 
    payment_reference,
    qris_image_url,
    expired_at
) VALUES (?, 'qrispay', ?, ?, ?, 'pending', ?, ?, ?);
```

---

## 3. Saweria Donation ID

### Format
UUID dari Saweria API

### Contoh
```
donation_abc123def456
saw_123456789
```

### Kegunaan
- Identifikasi unik untuk donasi Saweria
- Digunakan untuk check status pembayaran
- Link ke payment URL Saweria
- Tracking donation history

### Query Payment Status
```php
// Check Saweria payment status
$saweria = new SaweriaAPI();
$donation = $saweria->getDonation($donationId);
$isPaid = $saweria->isDonationPaid($donationId);
```

### Database Storage
```sql
INSERT INTO payments (
    user_id, 
    payment_method, 
    external_id,  -- Donation ID dari Saweria
    amount, 
    tickets, 
    status, 
    payment_reference,
    payment_url
) VALUES (?, 'saweria', ?, ?, ?, 'pending', ?, ?);
```

---

## 4. Tracking Number ID (tn_id)

### Format dari TrackTaco API
```
tn_{carrier}_{random_string}
```

### Contoh
```
tn_fedex_AbCdEf12_xYz9PqRs
tn_ups_QwErTy34_aB12
tn_dhl_MnOpQr56_cD34
```

### Kegunaan
- Identifikasi tracking number dalam sistem TrackTaco
- Digunakan untuk reveal tracking number
- 1 tn_id = 1 credit untuk reveal
- Setelah reveal, tracking number asli akan ditampilkan

### Search & Reveal Flow
```javascript
// 1. Search for tracking numbers
const searchResponse = await fetch('/api/search.php', {
    method: 'POST',
    body: JSON.stringify({
        filter: {
            carrier: ['fedex'],
            dest: { country: 'US', state: 'CA' }
        }
    })
});

// Response contains tn_id
// {
//   "results": [{
//     "tn_id": "tn_fedex_AbCdEf12_xYz9",
//     "carrier": "fedex",
//     "status": "transit",
//     ...
//   }]
// }

// 2. Reveal tracking number (costs 1 credit)
const revealResponse = await fetch('/api/reveal.php', {
    method: 'POST',
    body: JSON.stringify({
        tn_ids: ['tn_fedex_AbCdEf12_xYz9']
    })
});

// Response contains actual tracking number
// {
//   "results": [{
//     "tn_id": "tn_fedex_AbCdEf12_xYz9",
//     "outcome": "revealed",
//     "tracking_number": "871512246087",  // Actual TN!
//     "carrier": "fedex",
//     ...
//   }]
// }
```

---

## 5. User ID

### Format
Auto-increment integer

### Contoh
```
1, 2, 3, 4, ...
```

### Kegunaan
- Identifikasi unik untuk setiap user
- Primary key di tabel users
- Digunakan dalam payment reference
- Link ke semua transaksi user

---

## 6. Payment ID (Database)

### Format
Auto-increment integer

### Contoh
```
1, 2, 3, 4, ...
```

### Kegunaan
- Primary key di tabel payments
- Internal tracking untuk pembayaran
- Link ke user_id

---

## Troubleshooting Payment dengan ID

### Case: QRIS Code Tidak Muncul

**ID yang dibutuhkan:** `qris_id`

**Debug Steps:**

1. **Check Database:**
```sql
SELECT * FROM payments 
WHERE payment_reference = 'TKY-3-1783164139';
```

Output:
```
id: 5
user_id: 3
qris_id: ccf5aef4-0863-46dd-91d7-4f2b058da7eb
qris_image_url: NULL  <-- PROBLEM!
amount: 250000
status: pending
created_at: 2026-07-04 18:22:00
expired_at: 2026-07-04 18:37:00
```

2. **Check QRIS API Response:**
```php
$qrisPay = new QRISPayAPI();
try {
    $response = $qrisPay->generateQRIS(250000, 'TKY-3-1783164139');
    error_log('QRIS Response: ' . json_encode($response));
} catch (Exception $e) {
    error_log('QRIS Error: ' . $e->getMessage());
}
```

3. **Possible Issues:**
   - `qris_image_url` is NULL in database
   - API response format berbeda dengan expected
   - API endpoint berubah
   - Network error ke QRIS Pay API

4. **Solution:**
Sudah diperbaiki di `QRISPayAPI.php`:
```php
// Normalize response format
return [
    'qris_id' => $qrisData['qris_id'] ?? $qrisData['id'] ?? null,
    'qris_image_url' => $qrisData['qris_image_url'] ?? 
                        $qrisData['image_url'] ?? 
                        $qrisData['qr_url'] ?? '',
    // Handle berbagai format response
];
```

---

### Case: Saweria Payment Failed

**ID yang dibutuhkan:** `external_id` (donation_id)

**Debug Steps:**

1. **Check Error Log:**
```
[2026-07-04 18:22:00] Payment Creation Error: Failed to generate Saweria payment: API request failed
```

2. **Check Saweria API:**
```php
$saweria = new SaweriaAPI();
try {
    $profile = $saweria->getProfile();
    error_log('Saweria Profile: ' . json_encode($profile));
    
    $donation = $saweria->createDonation(250000, 'Test', 'User');
    error_log('Saweria Donation: ' . json_encode($donation));
} catch (Exception $e) {
    error_log('Saweria Error: ' . $e->getMessage());
}
```

3. **Possible Issues:**
   - Invalid API token (expired JWT)
   - Wrong endpoint URL
   - Network error
   - Saweria API down

4. **Solution:**
Check token expiration:
```sql
SELECT setting_value FROM admin_settings 
WHERE setting_key = 'saweria_api_token';
```

JWT token format:
```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

Decode JWT to check expiration:
```bash
# Use jwt.io or jwt-cli
jwt decode YOUR_TOKEN
```

---

### Case: Tracking Payment Status

**ID yang dibutuhkan:** `qris_id` atau `external_id`

**Query:**

```sql
-- Get payment by QRIS ID
SELECT * FROM payments WHERE qris_id = 'ccf5aef4-0863-46dd-91d7-4f2b058da7eb';

-- Get payment by Saweria ID
SELECT * FROM payments WHERE external_id = 'donation_123456';

-- Get payment by Reference
SELECT * FROM payments WHERE payment_reference = 'TKY-3-1783164139';

-- Get user's payments
SELECT * FROM payments WHERE user_id = 3 ORDER BY created_at DESC;
```

**Check Status Flow:**

```javascript
// Frontend polling
setInterval(async () => {
    const response = await fetch(`/api/payment/check.php?qris_id=${qrisId}`);
    const data = await response.json();
    
    if (data.status === 'paid') {
        // Update UI
        // Reload credits
        // Show success message
    }
}, 3000);
```

---

## Payment Status Flow Diagram

```
[User clicks "Pay Now"]
         |
         v
[Create Payment Record] --> Generate Payment Reference (TKY-3-1783164139)
         |
         v
    [QRIS Path]                    [Saweria Path]
         |                               |
Generate QRIS ID                  Generate Donation ID
(ccf5aef4-0863...)               (donation_abc123...)
         |                               |
Show QR Code                      Redirect to Saweria URL
         |                               |
         v                               v
[User Scans & Pays]              [User Pays on Saweria]
         |                               |
         v                               v
[Poll Payment Status]            [Poll Payment Status]
every 3 seconds                   every 5 seconds
         |                               |
         v                               v
   [Status: paid]                  [Status: paid]
         |                               |
         +---------------+---------------+
                         |
                         v
              [Update Database]
              - status = 'paid'
              - paid_at = NOW()
                         |
                         v
              [Add Credits to User]
              - UPDATE users SET tickets = tickets + ?
                         |
                         v
              [Record Usage History]
              - INSERT INTO ticket_usage
                         |
                         v
              [Show Success Message]
```

---

## Quick Reference

| ID Type | Format | Example | Where Used |
|---------|--------|---------|------------|
| Payment Reference | `TKY-{user_id}-{timestamp}` | TKY-3-1783164139 | QRIS, Saweria, Database |
| QRIS ID | UUID | ccf5aef4-0863-46dd... | QRIS Pay API, Database |
| Saweria ID | String/UUID | donation_abc123 | Saweria API, Database |
| TN ID | `tn_{carrier}_{string}` | tn_fedex_AbCd... | TrackTaco API |
| User ID | Integer | 1, 2, 3 | Database |
| Payment ID | Integer | 1, 2, 3 | Database (internal) |

---

## Testing dengan ID

### Test QRIS Payment:
```bash
# Create test payment
curl -X POST http://localhost/api/payment/create.php \
  -H "Content-Type: application/json" \
  -d '{
    "credits": 5,
    "amount": 250000,
    "total": 5,
    "payment_method": "qrispay"
  }'

# Response akan berisi qris_id
# Simpan qris_id untuk checking status
```

### Check Payment Status:
```bash
curl "http://localhost/api/payment/check.php?qris_id=ccf5aef4-0863-46dd-91d7-4f2b058da7eb"
```

---

Last Updated: July 4, 2026
