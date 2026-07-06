# Visual Guide - Tracking Filter System

## 🎯 What User Will See

### Step 1: Opening the Date Range Modal
```
┌─────────────────────────────────────────┐
│ LEFT SIDEBAR - FILTERS                  │
├─────────────────────────────────────────┤
│                                         │
│ SHIP DATE WINDOW                        │
│                                         │
│ [Select date range...     📅]   ← CLICK │
│                                         │
│ ℹ️ Click to open calendar              │
│   with availability                     │
│                                         │
└─────────────────────────────────────────┘
```

### Step 2: Modal Opens with Date Inputs
```
┌──────────────────────────────────────┐
│   Select Ship Date Range          ✕  │
├──────────────────────────────────────┤
│   Choose start and end dates         │
│                                      │
│   ┌─────────────────────────────────┐│
│   │ ┌──────────────┐ ┌────────────┐ ││
│   │ │ From Date    │ │ To Date    │ ││
│   │ │              │ │            │ ││
│   │ │ [2026-07-01] │ │[2026-07-09]│ ││
│   │ └──────────────┘ └────────────┘ ││
│   └─────────────────────────────────┘│
│                                      │
│   No date selected                   │
│   (Will show: Selected: Jul 1 → Jul 9)│
│                                      │
│         [Cancel] [Apply Date Range] │
└──────────────────────────────────────┘
```

### Step 3: Dates Selected, Range Shown
```
┌──────────────────────────────────────┐
│   Select Ship Date Range          ✕  │
├──────────────────────────────────────┤
│   Choose start and end dates         │
│                                      │
│   ┌─────────────────────────────────┐│
│   │ ┌──────────────┐ ┌────────────┐ ││
│   │ │ From Date    │ │ To Date    │ ││
│   │ │              │ │            │ ││
│   │ │ [2026-07-01] │ │[2026-07-09]│ ││
│   │ └──────────────┘ └────────────┘ ││
│   └─────────────────────────────────┘│
│                                      │
│   Selected: Jul 1 → Jul 9 ✓          │
│   (Status berubah, visual feedback)  │
│                                      │
│         [Cancel] [Apply Date Range] │
└──────────────────────────────────────┘
      ↓ User clicks "Apply Date Range"
```

### Step 4: Back to Filter Panel (Updated)
```
┌─────────────────────────────────────────────┐
│ LEFT SIDEBAR - FILTERS                      │
├─────────────────────────────────────────────┤
│                                             │
│ SHIP DATE WINDOW                            │
│                                             │
│ [Jul 01 - Jul 09          📅]  ← UPDATED   │
│  ↑ Shows selected range                     │
│                                             │
│ ℹ️ Click to open calendar                  │
│   with availability                         │
│                                             │
│                                             │
│ EST. DELIVERY WINDOW  (NEW!)                │
│                                             │
│ From: [2026-07-05]                          │
│ To:   [2026-07-15]                          │
│                                             │
│ ℹ️ Pre-transit labels have no estimate yet │
│                                             │
│                                             │
│   [Search Tracking Numbers]                 │
│   [Reset Filters]                           │
│                                             │
└─────────────────────────────────────────────┘
```

### Step 5: Results Table (After Filtering)
```
┌──────────────────────────────────────────────────────────────────────────────────────────┐
│ RESULTS                                                                                   │
├──────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                          │
│ Showing 15 of 142 results                                                               │
│                                                                                          │
│ ┌────────┬────────┬────────┬──────────────┬───────────┬──────────────┬────────┬──────────┐
│ │Carrier │ Status │ Origin │ Destination  │ Ship Date │ Est. Delivery│ Weight │Candidates│
│ ├────────┼────────┼────────┼──────────────┼───────────┼──────────────┼────────┼──────────┤
│ │ FEDEX  │Transit │NEW YORK│LOS ANGELES   │Jul 05     │Jul 08        │2.0 lbs │Ship: Jul5│
│ │        │        │USA     │CA USA        │Ship       │Est. Delivery │        │Est.Del:  │
│ │        │        │        │              │           │              │        │Jul 08    │
│ ├────────┼────────┼────────┼──────────────┼───────────┼──────────────┼────────┼──────────┤
│ │ DHL    │Transit │SHANGHAI│LOS ANGELES   │Jul 03     │Jul 09        │1.5 lbs │Ship: Jul3│
│ │        │        │CHINA   │CA USA        │Ship       │Est. Delivery │        │Est.Del:  │
│ │        │        │        │              │           │              │        │Jul 9     │
│ ├────────┼────────┼────────┼──────────────┼───────────┼──────────────┼────────┼──────────┤
│ │ UPS    │Pre-T.. │CHICAGO │NEW YORK      │Jul 07     │Jul 10        │3.2 lbs │Ship: Jul7│
│ │        │        │USA     │NY USA        │Ship       │Est. Delivery │        │Est.Del:  │
│ │        │        │        │              │           │              │        │Jul 10    │
│ ├────────┼────────┼────────┼──────────────┼───────────┼──────────────┼────────┼──────────┤
│ │ FEDEX  │Transit │MIAMI   │SEATTLE       │Jul 02     │Jul 08        │2.8 lbs │Ship: Jul2│
│ │        │        │USA     │WA USA        │Ship       │Est. Delivery │        │Est.Del:  │
│ │        │        │        │              │           │              │        │Jul 8     │
│ └────────┴────────┴────────┴──────────────┴───────────┴──────────────┴────────┴──────────┘
                     ↑ SHIP DATE Column    ↑ EST. DELIVERY Column  ↑ CANDIDATES COLUMN (NEW)
                       (Shows ship date)     (Shows est delivery)    (Explains match reason)
│
│ Load More Results →
│
└──────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📝 Filter Flow Diagram

```
USER INPUT                    DATA PROCESSING              API REQUEST              RESULTS
═══════════════════════════════════════════════════════════════════════════════════════════

[SHIP DATE WINDOW]
From: Jul 1 ────────┐         gatherFilters()            buildFilter()          Only show
To: Jul 9 ──────────┼────────→ ├─ ship_from         ────→ ├─ shipped_between ──→ shipments
                    │         │  ship_to                 │   {from, to}         with
[EST.DELIVERY WNDW] │         └─ delivery_from    ────→ ├─ est_delivery_between ship_date
From: Jul 5 ────────┼────────→    delivery_to           │   {from, to}         1-9 Jul
To: Jul 15 ─────────┘         │                         └───────────────┐       AND
                               │                                        │       est_delivery
[CARRIERS]                     └──→ JSON POST to /api/search ──────────→ TrackTaco API        5-15 Jul
FedEx, DHL, UPS ───────────────→                                         
                                                                     ↓ Response with
[STATUS]                                                            matching TNs
Pre-Transit ────────────────────→
Transit                                                        ┌─────────────────┐
Delivered                                                      │ Result Row:     │
                                                              │ Ship: Jul 05    │
[ORIGIN]                                                      │ Est.Del: Jul 08 │
Country: US ────────────────────→                             └─────────────────┘
City: New York                                                 (Why it matches)
                                                              
[DESTINATION]                                                Display in table
Country: US ────────────────────→                             with explanation
State: CA
City: LA
```

---

## 🎬 Step-by-Step Example

### Scenario: Search for shipments sent July 2-7 that arrive by July 10

**User Actions:**
```
1. Navigate to /track
2. Click "Select date range..." button
   → Modal opens
3. Set From Date: 2026-07-02
4. Set To Date: 2026-07-07
5. Click "Apply Date Range"
   → Modal closes, button shows "Jul 02 - Jul 07"
6. In EST. DELIVERY WINDOW:
   Set From: 2026-07-01
   Set To: 2026-07-10
7. Click "Search Tracking Numbers"
```

**What Happens Behind Scenes:**
```
JavaScript:
  ship_from = "2026-07-02"
  ship_to = "2026-07-07"
  delivery_from = "2026-07-01"
  delivery_to = "2026-07-10"

↓

API Request:
  POST /api/search
  {
    "ship_from": "2026-07-02",
    "ship_to": "2026-07-07",
    "delivery_from": "2026-07-01",
    "delivery_to": "2026-07-10"
  }

↓

Server Filter:
  TukeruyAPI::buildFilter():
    shipped_between: {from: "2026-07-02", to: "2026-07-07"}
    est_delivery_between: {from: "2026-07-01", to: "2026-07-10"}

↓

TrackTaco API Query:
  Returns only TNs where:
    ship_date >= 2026-07-02 AND ship_date <= 2026-07-07
    est_delivery >= 2026-07-01 AND est_delivery <= 2026-07-10

↓

Results Display:
  Each row shows:
  - Ship Date: Jul 04
  - Est. Delivery: Jul 09
  - Candidates: "Ship: Jul 04 | Est. Delivery: Jul 09"
```

**Expected Results:**
- Only shipments sent between July 2-7 showing
- Only those with delivery estimates by July 10
- Example match: Shipped Jul 04, arrives Jul 09 ✓

---

## 💡 Understanding "Candidates" Column

The "Candidates" column explains **WHY each tracking number appears in results**.

### Examples:

```
Filter Set: Ship 1-9 Jul, Delivery 5-15 Jul

Row 1:
  Ship Date: Jul 05
  Est. Delivery: Jul 08
  Candidates: "Ship: Jul 05 | Est. Delivery: Jul 08"
  Status: ✓ MATCH (ship 5 in 1-9, delivery 8 in 5-15)

Row 2:
  Ship Date: Jul 02
  Est. Delivery: Jul 10
  Candidates: "Ship: Jul 02 | Est. Delivery: Jul 10"
  Status: ✓ MATCH (ship 2 in 1-9, delivery 10 in 5-15)

Row 3:
  Ship Date: N/A
  Est. Delivery: Jul 07
  Candidates: "Est. Delivery: Jul 07"
  Status: ✓ MATCH (delivery 7 in 5-15, ship unknown)

Row 4:
  Ship Date: Jul 04
  Est. Delivery: N/A
  Candidates: "Ship: Jul 04"
  Status: ✓ MATCH (ship 4 in 1-9, delivery unknown)
```

---

## 🔍 Console Logs (Developer Verification)

When user applies filters, check browser console (F12):

```javascript
✓ Console Output:
  🚀 Setting up ship date range picker...
  ✅ Ship date range picker ready
  
✓ When user applies date range:
  📅 From date changed: 2026-07-01
  📅 To date changed: 2026-07-09
  ✅ Ship date range applied: 2026-07-01 - 2026-07-09

✓ When search is triggered:
  📋 Search API: Filters received - 
  {
    "ship_from": "2026-07-01",
    "ship_to": "2026-07-09",
    "delivery_from": "2026-07-05",
    "delivery_to": "2026-07-15"
  }
  ✅ Search API: Found 142 results
```

---

## 🎉 Summary

**Flow bekerja seperti ini:**

1. **User sets date range** (Ship Date 1-9, Delivery 5-15)
2. **JavaScript collects filters** (gatherFilters)
3. **Sends to API** (/api/search dengan JSON filters)
4. **Server builds TrackTaco query** (shipped_between + est_delivery_between)
5. **TrackTaco returns matching TNs** (hanya yang dalam range)
6. **Results displayed dengan explanation** (Ship: Jul 05 | Est. Delivery: Jul 08)
7. **User dapat lihat kenapa setiap TN muncul** (Candidates column)

**SEMUA SUDAH SIAP PAKAI!**
