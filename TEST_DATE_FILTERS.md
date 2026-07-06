# Test Date Filters - Quick Guide

## Test Cases

### Test 1: Ship Date Filter Only ✅
**Steps**:
1. Buka halaman track.php
2. Scroll ke filter sidebar
3. Klik button "SHIP DATE WINDOW" (purple icon)
4. Pilih tanggal: 25 Juni 2026 - 30 Juni 2026
5. Klik Apply/Enter

**Expected Result**:
- Display berubah: "Jun 25, 2026 - Jun 30, 2026"
- API dipanggil dengan parameter:
  ```json
  {
    "filter": {
      "shipped_between": {
        "from": "2026-06-25",
        "to": "2026-06-30"
      }
    }
  }
  ```
- Result: Paket yang dikirim tanggal 25-30 Juni muncul
- Notifikasi muncul: "Ship date range: Jun 25, 2026 - Jun 30, 2026"

---

### Test 2: Delivery Date Filter Only ✅
**Steps**:
1. Clear semua filter dulu (klik Reset)
2. Klik button "EST. DELIVERY WINDOW" (blue icon)
3. Pilih tanggal: 27 Juni 2026 - 30 Juni 2026
4. Klik Apply/Enter

**Expected Result**:
- Display berubah: "Jun 27, 2026 - Jun 30, 2026"
- API dipanggil dengan parameter:
  ```json
  {
    "filter": {
      "est_delivery_between": {
        "from": "2026-06-27",
        "to": "2026-06-30"
      }
    }
  }
  ```
- Result: Paket dengan estimasi tiba 27-30 Juni muncul (bisa dikirim kapan saja, misalnya 18-30 Juni)
- Notifikasi muncul: "Delivery date range: Jun 27, 2026 - Jun 30, 2026"

---

### Test 3: Both Filters Combined ✅
**Steps**:
1. Set Ship Date: 25 Juni - 30 Juni
2. Set Delivery Date: 27 Juni - 30 Juni
3. Klik Search/Apply

**Expected Result**:
- Kedua display terisi
- API dipanggil dengan parameter:
  ```json
  {
    "filter": {
      "shipped_between": {
        "from": "2026-06-25",
        "to": "2026-06-30"
      },
      "est_delivery_between": {
        "from": "2026-06-27",
        "to": "2026-06-30"
      }
    }
  }
  ```
- Result: Paket yang dikirim 25-30 Juni DAN estimasi tiba 27-30 Juni

---

### Test 4: Clear Ship Date ✅
**Steps**:
1. Set ship date range
2. Klik tombol X (clear) di samping "SHIP DATE WINDOW"

**Expected Result**:
- Ship date display kembali ke "Select date range..."
- Delivery date tetap ada (jika sebelumnya di-set)
- Auto-apply triggered
- Notifikasi: "Ship date range cleared"

---

### Test 5: Clear Delivery Date ✅
**Steps**:
1. Set delivery date range
2. Klik tombol X (clear) di samping "EST. DELIVERY WINDOW"

**Expected Result**:
- Delivery date display kembali ke "Select date range..."
- Ship date tetap ada (jika sebelumnya di-set)
- Auto-apply triggered
- Notifikasi: "Delivery date range cleared"

---

### Test 6: Reset All Filters ✅
**Steps**:
1. Set ship date dan delivery date
2. Klik button "Reset"

**Expected Result**:
- Ship date cleared
- Delivery date cleared
- Semua filter lain juga di-reset
- Notifikasi: "Filters reset"

---

## Browser Console Testing

Open browser console (F12) dan cek:

```javascript
// Check ship date values
console.log('Ship From:', document.getElementById('ship_from').value);
console.log('Ship To:', document.getElementById('ship_to').value);

// Check delivery date values
console.log('Delivery From:', document.getElementById('delivery_from').value);
console.log('Delivery To:', document.getElementById('delivery_to').value);

// Manually trigger ship date calendar
toggleShipDateCalendar();

// Manually trigger delivery date calendar
toggleDeliveryDateCalendar();

// Clear ship date
clearShipDateRange();

// Clear delivery date
clearDeliveryDateRange();
```

---

## API Testing dengan Postman/cURL

### Ship Date Only:
```bash
curl -X POST http://localhost/api/search \
  -H "Content-Type: application/json" \
  -d '{
    "filters": {
      "ship_from": "2026-06-25",
      "ship_to": "2026-06-30"
    }
  }'
```

### Delivery Date Only:
```bash
curl -X POST http://localhost/api/search \
  -H "Content-Type: application/json" \
  -d '{
    "filters": {
      "delivery_from": "2026-06-27",
      "delivery_to": "2026-06-30"
    }
  }'
```

### Both:
```bash
curl -X POST http://localhost/api/search \
  -H "Content-Type: application/json" \
  -d '{
    "filters": {
      "ship_from": "2026-06-25",
      "ship_to": "2026-06-30",
      "delivery_from": "2026-06-27",
      "delivery_to": "2026-06-30"
    }
  }'
```

---

## Visual Check

### Ship Date Window (Purple):
- [ ] Icon purple calendar visible
- [ ] Label "SHIP DATE WINDOW" in uppercase
- [ ] Clear button (X) visible di kanan
- [ ] Button style consistent dengan design
- [ ] Hover effect works (border purple)
- [ ] Display text changes on selection

### Delivery Date Window (Blue):
- [ ] Icon blue clock visible
- [ ] Label "EST. DELIVERY WINDOW" in uppercase
- [ ] Clear button (X) visible di kanan
- [ ] Help text "Filter by estimated arrival date" visible
- [ ] Button style consistent dengan design
- [ ] Hover effect works (border blue)
- [ ] Display text changes on selection

---

## Edge Cases

### 1. Invalid Date Range:
- Start date > End date
- Expected: Alert message "Start date must be before end date"

### 2. Empty Selection:
- Click apply tanpa pilih tanggal
- Expected: Alert "Please select both start and end dates"

### 3. Pre-transit Packages:
- Filter dengan delivery date
- Expected: Pre-transit packages tidak muncul (karena tidak punya estimate)

### 4. Auto-apply Disabled:
- Matikan auto-apply toggle
- Change date filter
- Expected: Search tidak otomatis triggered, butuh klik Search button

---

## Performance Check

- [ ] Page load time acceptable
- [ ] Calendar open/close smooth
- [ ] No console errors
- [ ] Date selection responsive
- [ ] Auto-apply debounced properly (tidak multiple request)
- [ ] Filter clear instantaneous

---

## Status: Ready for Testing ✅

Semua file sudah diupdate, tinggal test di browser:
1. Buka `track.php`
2. Test semua scenario di atas
3. Cek console untuk error
4. Verifikasi API request di Network tab

**Good luck testing! 🚀**
