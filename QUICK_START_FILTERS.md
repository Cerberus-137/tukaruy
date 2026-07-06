# Quick Start - Tracking Filters (UPDATED)

## ✨ What Changed?

Added **Delivery Date column** and **Candidates explanation** to tracking results. Now user dapat lihat:
- ✅ Ship Date (kapan dikirim)
- ✅ Est. Delivery (perkiraan tiba)
- ✅ Why it matches (alasan di list)

---

## 🎯 How to Use

### 1️⃣ Set Ship Date Range (kapan barang dikirim)
```
Go to /track
↓
Scroll to "SHIP DATE WINDOW"
↓
Click "Select date range..." button
↓
Modal opens dengan 2 date inputs
  From Date: [picker] ← Click, choose date
  To Date:   [picker] ← Click, choose date
↓
Click "Apply Date Range"
↓
Button shows: "Jul 01 - Jul 09" ✓
```

### 2️⃣ Set Delivery Date Range (perkiraan tiba)
```
Scroll ke "EST. DELIVERY WINDOW" (baru)
↓
From: [2026-07-05] ← Set start delivery date
To:   [2026-07-15] ← Set end delivery date
↓
Leave empty jika tidak mau filter delivery date
```

### 3️⃣ Set Other Filters (optional)
```
CARRIER: FedEx, DHL, UPS
STATUS: Pre-Transit, Transit, Delivered
ORIGIN: Country → City
DESTINATION: Country → State → City
```

### 4️⃣ Search
```
Click "Search Tracking Numbers" button
↓
Results appear in table dengan:
  Carrier | Status | Origin | Destination | Ship Date | Est. Delivery | Weight | Candidates | Action
                                              ↑ Baru        ↑ Baru          ↑ Baru
```

---

## 📊 Understanding Results

### Example Result Row:

```
Carrier: FEDEX
Status: Transit
Origin: NEW YORK, USA
Destination: LOS ANGELES, CA USA
Ship Date: Jul 05
Est. Delivery: Jul 08
Weight: 2.0 lbs
Candidates: Ship: Jul 05 | Est. Delivery: Jul 08  ← EXPLANATION
Action: [Get TN]
```

**Candidates Column explanation:**
- `Ship: Jul 05` = Barang ini dikirim tanggal 5 Juli
- `Est. Delivery: Jul 08` = Perkiraan tiba tanggal 8 Juli
- Shows WHY this tracking is in your results

---

## 🔍 Filter Examples

### Example 1: "Show shipments sent 1-9 July"
```
SHIP DATE WINDOW: Jul 01 - Jul 09
EST. DELIVERY WINDOW: (leave empty)
↓
Results: All tracking numbers dengan ship_date between July 1-9
Regardless of delivery date
```

### Example 2: "Show shipments arriving 5-15 July"
```
SHIP DATE WINDOW: (leave empty)
EST. DELIVERY WINDOW: Jul 05 - Jul 15
↓
Results: All tracking numbers dengan est_delivery between July 5-15
Regardless of ship date
```

### Example 3: "Shipments sent 1-9 July, arriving by 15 July"
```
SHIP DATE WINDOW: Jul 01 - Jul 09
EST. DELIVERY WINDOW: Jul 01 - Jul 15
↓
Results: Tracking numbers dengan:
  ship_date: 1-9 July AND
  est_delivery: 1-15 July
```

---

## 🛠️ Troubleshooting

| Problem | Solution |
|---------|----------|
| Modal not opening | Check if button has onclick handler. F12 console untuk error. |
| "Please select both dates" | Fill BOTH From Date AND To Date fields sebelum Apply. |
| No results shown | Try dengan date range yang lebih luas (e.g., 1-30 July). |
| Candidates column kosong | API mungkin tidak return ship_date/est_delivery. Contact admin. |
| Results tidak update | Toggle Auto-Apply ON atau klik Search button setelah update filters. |

---

## 🎮 Advanced: Auto-Apply Toggle

```
Top right area:
[≡ Auto-Apply]

ON:  Filters applied automatically setiap kali user ubah value
OFF: User harus klik "Search Tracking Numbers" button manually
```

---

## 📝 Important Notes

1. **Date Format**: Sistem menggunakan YYYY-MM-DD internally
   - Display: Jul 05 (user-friendly)
   - Internal: 2026-07-05

2. **Blank Fields = No Filter**
   - Delivery Date kosong = search semua delivery dates
   - Ship Date kosong = search semua ship dates

3. **Pre-Transit Labels**
   - May not have est_delivery data
   - Will still appear in results
   - "Candidates" column mungkin hanya show "Ship: Jul 05"

4. **Performance**
   - Large date ranges mungkin return banyak results
   - Use more specific filters untuk better results
   - Pagination otomatis dengan "Load More" button

---

## ✅ Checklist Before Testing

- [ ] Date range modal opens when button clicked
- [ ] Can select From Date dan To Date
- [ ] Button shows selected range after Apply
- [ ] EST. DELIVERY WINDOW has 2 input fields (From/To)
- [ ] Search results show all 9 columns
- [ ] Ship Date column populated
- [ ] Est. Delivery column populated
- [ ] Candidates column shows match explanation
- [ ] Table header has 9 columns (not 7)

---

## 🚀 Ready!

Sistem sudah complete dan ready to use.

**Go to /track dan test sekarang!**

Questions atau issues? Check console (F12) untuk detailed logs.
