# Quick Test Script - Verify All Fixes

Copy & paste these commands in browser console (F12) to verify fixes are working.

---

## Test 1: Check Ship Dates API

```javascript
// Test API endpoint
fetch('/api/ship-dates')
  .then(r => r.json())
  .then(data => {
    console.log('API Status:', data.success ? '✅ OK' : '❌ FAILED');
    console.log('Dates Found:', data.dates.length);
    console.log('Sample:', data.dates.slice(0, 3));
  })
  .catch(e => console.error('❌ API Error:', e));
```

**Expected Output**:
```
API Status: ✅ OK
Dates Found: 45
Sample: [
  {date: "2026-07-01", count: 5},
  {date: "2026-07-02", count: 12},
  ...
]
```

---

## Test 2: Check if Calendar Initialized

```javascript
// Check calendar status
console.log('Calendar Exists:', !!shipDatePicker);
console.log('Available Dates:', window.shipDatesData ? Object.keys(window.shipDatesData).length : 'Not loaded');
if (shipDatePicker) {
  console.log('Calendar Config:', {
    mode: shipDatePicker.config.mode,
    inline: shipDatePicker.config.inline
  });
}
```

**Expected Output**:
```
Calendar Exists: true
Available Dates: 45
Calendar Config: {mode: "range", inline: true}
```

---

## Test 3: Check Timeline Data

```javascript
// Simulate reveal modal data
const testData = {
  tracking_number: '873888307824',
  carrier: 'fedex',
  ship_date: '2026-07-02',
  delivery_date: '2026-07-06',
  status: 'transit'
};

console.log('Timeline Data:', {
  shipDate: testData.ship_date,
  deliveryDate: testData.delivery_date,
  duration: `${new Date(testData.ship_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})} → ${new Date(testData.delivery_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}`
});
```

**Expected Output**:
```
Timeline Data: {
  shipDate: "2026-07-02",
  deliveryDate: "2026-07-06",
  duration: "Jul 2 → Jul 6, 2026"
}
```

---

## Test 4: Verify Ship Date Picker Dates

```javascript
// Check if calendar has dates
const calendarDays = document.querySelectorAll('.flatpickr-day');
const enabledDays = Array.from(calendarDays).filter(d => !d.classList.contains('flatpickr-disabled'));
const daysWithBadges = Array.from(calendarDays).filter(d => d.classList.contains('has-tn'));

console.log('Calendar Analysis:', {
  totalDays: calendarDays.length,
  enabledDays: enabledDays.length,
  daysWithBadges: daysWithBadges.length,
  sampleBadges: daysWithBadges.slice(0, 3).map(d => ({
    date: d.textContent.trim(),
    count: d.getAttribute('data-count'),
    title: d.title
  }))
});
```

**Expected Output**:
```
Calendar Analysis: {
  totalDays: 84,
  enabledDays: 45,
  daysWithBadges: 45,
  sampleBadges: [
    {date: "1", count: "5", title: "5 tracking numbers"},
    {date: "2", count: "12", title: "12 tracking numbers"},
    ...
  ]
}
```

---

## Test 5: Test Carrier Link Generation

```javascript
// Test carrier link function
const fedexLink = generateCarrierLink('fedex', '873888307824');
const dhlLink = generateCarrierLink('dhl', '873888307824');
const upsLink = generateCarrierLink('ups', '873888307824');

console.log('FedEx Link Generated:', !!fedexLink);
console.log('DHL Link Generated:', !!dhlLink);
console.log('UPS Link Generated:', !!upsLink);

// Check URLs
console.log('FedEx URL:', fedexLink.includes('fedex.com') ? '✅' : '❌');
console.log('DHL URL:', dhlLink.includes('dhl.com') ? '✅' : '❌');
console.log('UPS URL:', upsLink.includes('ups.com') ? '✅' : '❌');
```

**Expected Output**:
```
FedEx Link Generated: true
DHL Link Generated: true
UPS Link Generated: true
FedEx URL: ✅
DHL URL: ✅
UPS URL: ✅
```

---

## Test 6: Test User Menu

```javascript
// Check user menu setup
const menuBtn = document.getElementById('user-menu-btn');
const menu = document.getElementById('user-menu');

console.log('Menu Button Found:', !!menuBtn);
console.log('Menu Element Found:', !!menu);
console.log('Menu Hidden:', menu?.classList.contains('hidden'));

// Try clicking
if (menuBtn && menu) {
  menuBtn.click();
  setTimeout(() => {
    console.log('After Click - Menu Hidden:', menu.classList.contains('hidden'));
    menuBtn.click(); // Click again to close
  }, 100);
}
```

**Expected Output**:
```
Menu Button Found: true
Menu Element Found: true
Menu Hidden: true
After Click - Menu Hidden: false
```

---

## Visual Tests

### Test Calendar Display
1. Go to `/track` page
2. Click "Select date range..." button
3. **Verify**:
   - ✅ Calendar appears
   - ✅ Two months shown side-by-side
   - ✅ Some days are clickable (white text)
   - ✅ Some days are disabled (gray text)
   - ✅ Some days have badges with numbers

### Test Timeline Display
1. Go to `/track` page
2. Click "Get TN" button on any tracking number
3. **Verify**:
   - ✅ Modal opens
   - ✅ "Shipment" section shows at top
   - ✅ Date range visible (e.g., "Jul 02 → Jul 06 2026")
   - ✅ Blue progress bar visible
   - ✅ Ship Date and Delivery Date fields below

### Test Menu Function
1. On any page, click profile button (👤) top right
2. **Verify**:
   - ✅ Menu opens
   - ✅ Click Settings → menu closes + navigate
   - ✅ Click profile button → menu opens
   - ✅ Click outside → menu closes

---

## Troubleshooting

If any test fails:

1. **Check Browser Console** (F12):
   - Look for red error messages
   - Look for warning messages
   - Look for emoji-prefixed logs

2. **Clear Cache**:
   ```
   Ctrl+Shift+Del
   Select "Cached images and files"
   Clear for "All time"
   ```

3. **Hard Refresh**:
   ```
   Ctrl+F5
   ```

4. **Check Network**:
   - F12 → Network tab
   - Refresh page
   - Look for `/api/ship-dates` request
   - Check response (should have dates array)

---

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Calendar empty | Check API response, clear cache |
| No badges on dates | API returns no dates, check data |
| Menu not clickable | Hard refresh, clear cache |
| Timeline missing | Refresh page, check modal HTML |
| API error | Check server logs, verify auth |

---

## When Everything Works

You should see:

✅ Calendar with dates and badges  
✅ Timeline showing ship → delivery dates  
✅ User menu closes on click  
✅ Carrier links in Get TN modal  
✅ All console logs with emoji markers  

**If all tests pass** → System is ready for production! 🎉

