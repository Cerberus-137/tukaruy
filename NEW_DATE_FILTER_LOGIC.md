# New Date Filter Logic - Single Date Window
**Date**: 6 Juli 2026  
**Version**: 2.0

---

## Overview

Filter tanggal sekarang menggunakan **SINGLE DATE** (bukan range):

1. **SHIP DATE WINDOW** = Single date (tanggal mulai pengiriman)
2. **EST. DELIVERY WINDOW** = Single date (tanggal estimasi tiba)

---

## Logic Flow

### Scenario 1: Ship Date Only ✅
**Input**:
- Ship Date: 1 Juni
- Delivery Date: (empty)

**Filter Logic**:
```javascript
{
  "ship_from": "2026-06-01",
  "ship_to": "2026-06-01"
}
```

**Result**:
- Cari paket yang **dikirim TEPAT** tanggal 1 Juni
- Exact match: `ship_date = 2026-06-01`

---

### Scenario 2: Delivery Date Only ✅
**Input**:
- Ship Date: (empty)
- Delivery Date: 5 Juni

**Filter Logic**:
```javascript
{
  "delivery_from": "2026-06-05",
  "delivery_to": "2026-06-05"
}
```

**Result**:
- Cari paket yang **estimasi tiba TEPAT** tanggal 5 Juni
- Exact match: `est_delivery_date = 2026-06-05`

---

### Scenario 3: Both Dates (Range Logic) ✅
**Input**:
- Ship Date: 1 Juni
- Delivery Date: 5 Juni

**Filter Logic**:
```javascript
{
  "ship_from": "2026-06-01",  // FROM ship date
  "ship_to": "2026-06-05"     // TO delivery date
}
```

**Result**:
- Cari paket yang **dikirim ANTARA** tanggal 1 Juni - 5 Juni
- Range match: `ship_date >= 2026-06-01 AND ship_date <= 2026-06-05`
- **TIDAK pakai** `est_delivery_date` filter

**Candidates yang akan muncul**:
- Ship Date: 1 Juni (1-5) ✅
- Ship Date: 2 Juni (2-5) ✅
- Ship Date: 3 Juni (3-5) ✅
- Ship Date: 4 Juni (4-5) ✅
- Ship Date: 5 Juni (5-5) ✅
- Ship Date: 6 Juni ❌ (di luar range)

---

## UI Design

### Ship Date Window (Purple 🟣)
```html
<label>
    <i class="fas fa-calendar-alt text-purple-400"></i>
    SHIP DATE WINDOW
    <button onclick="clearShipDate()">×</button>
</label>
<input type="date" id="ship_date" class="modern-input">
<p>Select shipment start date</p>
```

### Est. Delivery Window (Blue 🔵)
```html
<label>
    <i class="fas fa-clock text-blue-400"></i>
    EST. DELIVERY WINDOW
    <button onclick="clearDeliveryDate()">×</button>
</label>
<input type="date" id="delivery_date" class="modern-input">
<p>Select estimated arrival date</p>
```

---

## JavaScript Implementation

### Gather Filters Function
```javascript
function gatherFilters() {
    const filters = {};
    
    const shipDate = document.getElementById('ship_date')?.value;
    const deliveryDate = document.getElementById('delivery_date')?.value;
    
    // Logic 1: Both dates → Range from ship to delivery
    if (shipDate && deliveryDate) {
        filters.ship_from = shipDate;
        filters.ship_to = deliveryDate;
        // Don't use delivery filter
        filters.delivery_from = null;
        filters.delivery_to = null;
    } 
    // Logic 2: Ship date only → Exact match
    else if (shipDate) {
        filters.ship_from = shipDate;
        filters.ship_to = shipDate;
    } 
    // Logic 3: Delivery date only → Exact match
    else if (deliveryDate) {
        filters.delivery_from = deliveryDate;
        filters.delivery_to = deliveryDate;
    }
    
    return filters;
}
```

### Clear Functions
```javascript
// Clear ship date
window.clearShipDate = function() {
    document.getElementById('ship_date').value = '';
    showNotification('Ship date cleared', 'info');
    if (autoApply) debounceSearch();
};

// Clear delivery date
window.clearDeliveryDate = function() {
    document.getElementById('delivery_date').value = '';
    showNotification('Delivery date cleared', 'info');
    if (autoApply) debounceSearch();
};
```

---

## API Request Examples

### Example 1: Ship Date Only
**User Input**:
- Ship Date: 2026-06-01
- Delivery Date: (empty)

**API Request**:
```json
{
  "filters": {
    "carrier": ["fedex"],
    "dest_country": "US",
    "ship_from": "2026-06-01",
    "ship_to": "2026-06-01"
  }
}
```

**API Processing (TukeruyAPI.php)**:
```php
$filter->shipped_between = new stdClass();
$filter->shipped_between->from = "2026-06-01";
$filter->shipped_between->to = "2026-06-01";
```

**SQL Equivalent**:
```sql
WHERE ship_date = '2026-06-01'
```

---

### Example 2: Delivery Date Only
**User Input**:
- Ship Date: (empty)
- Delivery Date: 2026-06-05

**API Request**:
```json
{
  "filters": {
    "carrier": ["fedex"],
    "dest_country": "US",
    "delivery_from": "2026-06-05",
    "delivery_to": "2026-06-05"
  }
}
```

**API Processing**:
```php
$filter->est_delivery_between = new stdClass();
$filter->est_delivery_between->from = "2026-06-05";
$filter->est_delivery_between->to = "2026-06-05";
```

**SQL Equivalent**:
```sql
WHERE est_delivery_date = '2026-06-05'
```

---

### Example 3: Both Dates (Range)
**User Input**:
- Ship Date: 2026-06-01
- Delivery Date: 2026-06-05

**API Request**:
```json
{
  "filters": {
    "carrier": ["fedex"],
    "dest_country": "US",
    "ship_from": "2026-06-01",
    "ship_to": "2026-06-05"
  }
}
```

**API Processing**:
```php
$filter->shipped_between = new stdClass();
$filter->shipped_between->from = "2026-06-01";
$filter->shipped_between->to = "2026-06-05";
// NO est_delivery_between
```

**SQL Equivalent**:
```sql
WHERE ship_date >= '2026-06-01' AND ship_date <= '2026-06-05'
```

---

## Sample Data & Results

### Sample Shipments:
| TN    | Ship Date | Est. Delivery | Carrier | Destination    |
|-------|-----------|---------------|---------|----------------|
| TN001 | Jun 1     | Jun 27        | FedEx   | GUANGDONG, CN  |
| TN002 | Jun 2     | Jun 28        | FedEx   | GUANGDONG, CN  |
| TN003 | Jun 3     | Jun 29        | FedEx   | GUANGDONG, CN  |
| TN004 | Jun 4     | Jun 30        | FedEx   | GUANGDONG, CN  |
| TN005 | Jun 5     | Jul 1         | FedEx   | GUANGDONG, CN  |
| TN006 | Jun 6     | Jul 2         | FedEx   | GUANGDONG, CN  |

---

### Test 1: Ship Date = Jun 1
**Result**: TN001 only
- Filter: Ship date EXACT match Jun 1

---

### Test 2: Delivery Date = Jun 27
**Result**: TN001 only
- Filter: Delivery date EXACT match Jun 27

---

### Test 3: Ship = Jun 1, Delivery = Jun 5
**Result**: TN001, TN002, TN003, TN004, TN005
- Filter: Ship date BETWEEN Jun 1 - Jun 5
- Creates range: 1→2→3→4→5
- All candidates dalam range ini muncul

---

## Candidate Display

Sesuai screenshot yang diberikan, kolom **Shipment** harus menampilkan:

```
Jun 25 ──→ Jun 27 2026
  🔵 ━━━━━━━━━ 🔵
```

Format display:
- **Start Date**: Jun 25 (ship date)
- **Progress Bar**: Visual timeline
- **End Date**: Jun 27 2026 (delivery date)

---

## Benefits of New Logic

### ✅ Advantages:
1. **Simpler UI**: Single date input (bukan 2 input from/to)
2. **Clearer Intent**: 
   - Ship Date = "Cari yang dikirim tanggal ini"
   - Delivery Date = "Cari yang tiba tanggal ini"
   - Both = "Cari yang dikirim dari tanggal A sampai B"
3. **Flexible**: User bisa pilih salah satu atau keduanya
4. **Intuitive**: Range otomatis terbentuk kalau kedua diisi

---

## Testing Scenarios

### Test 1: Ship Date Only ✅
```
Input:
- Ship Date: 2026-06-01
- Delivery Date: (empty)

Expected:
- API: ship_from=2026-06-01, ship_to=2026-06-01
- Result: Paket dengan ship_date = 2026-06-01
```

### Test 2: Delivery Date Only ✅
```
Input:
- Ship Date: (empty)
- Delivery Date: 2026-06-05

Expected:
- API: delivery_from=2026-06-05, delivery_to=2026-06-05
- Result: Paket dengan est_delivery = 2026-06-05
```

### Test 3: Both Dates (Range) ✅
```
Input:
- Ship Date: 2026-06-01
- Delivery Date: 2026-06-05

Expected:
- API: ship_from=2026-06-01, ship_to=2026-06-05
- Result: Paket dengan ship_date antara 2026-06-01 sampai 2026-06-05
- Candidates: 1-5, 2-5, 3-5, 4-5, 5-5
```

### Test 4: Clear Ship Date ✅
```
Action: Click Clear (×) button pada Ship Date

Expected:
- ship_date input cleared
- Auto-apply triggered (if enabled)
- Notification: "Ship date cleared"
```

### Test 5: Clear Delivery Date ✅
```
Action: Click Clear (×) button pada Delivery Date

Expected:
- delivery_date input cleared
- Auto-apply triggered (if enabled)
- Notification: "Delivery date cleared"
```

---

## Files Modified

1. ✅ **track.php**
   - Changed from 2 inputs (from/to) to 1 input per filter
   - `ship_date` (single)
   - `delivery_date` (single)

2. ✅ **assets/js/app.js**
   - Updated `gatherFilters()` with new range logic
   - Updated `clearShipDate()` and `clearDeliveryDate()`
   - Updated `setupFilterChangeListeners()` 
   - Updated `resetFilters()`

3. ⚠️ **api/TukeruyAPI.php**
   - No changes needed (already supports ship_from/ship_to and delivery_from/delivery_to)

---

## Status: ✅ IMPLEMENTED

New date filter logic:
- ✅ Single date input per filter
- ✅ Ship date only → Exact match
- ✅ Delivery date only → Exact match
- ✅ Both dates → Range from ship to delivery
- ✅ Clear buttons functional
- ✅ Auto-apply working

**Ready for testing! 🚀**
