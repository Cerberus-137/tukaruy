# Filter Fix Guide - Track Page

## 🔧 Perbaikan yang Sudah Dilakukan

### 1. Enhanced Error Handling di `app.js`
- Menambahkan try-catch block untuk setiap setup function
- Setiap function sekarang akan log `✓` jika berhasil atau `✗` jika error
- Ini akan memudahkan identifikasi fungsi mana yang bermasalah

### 2. Detailed Console Logging
```javascript
console.log('=== Tukeruy Track Page Initializing ===');
// ... setiap setup function akan log statusnya
console.log('✓ Filter buttons setup');
console.log('✓ Country dropdown setup');
// ... dst
console.log('=== Tukeruy Track Page Initialized ===');
```

### 3. File Testing Dibuat
- `test-track-filter.html` - Test isolated untuk filter components
- `test-filter.html` - Test sederhana untuk dropdown
- `admin/diagnostic.php` - Diagnostic dashboard lengkap

## 🧪 Cara Mengecek Filter Yang Rusak

### Method 1: Check Console Browser
1. Buka https://www.tukarkuy.web.id/track
2. Tekan F12 untuk membuka DevTools
3. Pergi ke tab "Console"
4. Refresh halaman
5. Lihat log initialization:
   ```
   === Tukeruy Track Page Initializing ===
   ✓ Filter buttons setup
   ✓ Default status set
   ✓ Country dropdown setup
   ...
   === Tukeruy Track Page Initialized ===
   ```
6. **Cari symbol ✗** - Ini menandakan fungsi yang error
7. Lihat error message yang detail

### Method 2: Test Page Isolated
1. Buka https://www.tukarkuy.web.id/test-track-filter.html
2. Akan ada 3 test:
   - Test 1: Country Dropdown
   - Test 2: Ship Date Button
   - Test 3: Carrier Filter Buttons
3. Setiap test akan show log apakah berhasil atau error
4. Coba klik setiap element untuk test functionality

### Method 3: Check Specific Elements
Jalankan di Console Browser:
```javascript
// Check if all filter elements exist
console.log('Country trigger:', document.getElementById('country-dropdown-trigger'));
console.log('Ship date trigger:', document.getElementById('ship-date-trigger'));
console.log('Filter buttons:', document.querySelectorAll('.segmented-btn').length);
console.log('Dest city trigger:', document.getElementById('dest-city-dropdown-trigger'));
console.log('Origin country trigger:', document.getElementById('origin-country-dropdown-trigger'));
```

## 🐛 Common Issues & Solutions

### Issue 1: Dropdown tidak bisa diklik
**Symptom:** Dropdown tidak membuka ketika diklik

**Check:**
```javascript
// Di console browser
const trigger = document.getElementById('country-dropdown-trigger');
console.log('Trigger exists:', trigger !== null);
console.log('Has click listener:', getEventListeners(trigger)); // Chrome only
```

**Solution:**
- Pastikan element ada di DOM
- Check apakah ada element lain yang overlap (z-index issue)
- Check console untuk error JS

### Issue 2: Filter button tidak toggle
**Symptom:** Klik button tapi tidak ada respon

**Check:**
```javascript
// Di console browser
const btns = document.querySelectorAll('.segmented-btn');
console.log('Found buttons:', btns.length);
btns.forEach((btn, i) => {
    console.log(`Button ${i}:`, btn.dataset.type, btn.dataset.value);
});
```

**Solution:**
- Pastikan button punya attribute `data-type` dan `data-value`
- Check console untuk error di `setupFilterButtons()`

### Issue 3: Ship date button tidak clickable
**Symptom:** Button "Select date range..." tidak merespon

**Check:**
```javascript
// Di console browser
const btn = document.getElementById('ship-date-trigger');
console.log('Button exists:', btn !== null);
console.log('Button styles:', {
    cursor: btn.style.cursor,
    pointerEvents: btn.style.pointerEvents,
    zIndex: btn.style.zIndex
});
```

**Solution:**
- Already fixed with inline styles
- Check console untuk "Ship date trigger clicked!" ketika button diklik
- Jika tidak ada log, berarti event listener tidak ter-attach

### Issue 4: Auto-apply tidak jalan
**Symptom:** Perubahan filter tidak trigger search otomatis

**Check:**
```javascript
// Di console browser
console.log('Auto-apply enabled:', autoApply);
console.log('Search timeout:', searchTimeout);
```

**Solution:**
- Check apakah auto-apply toggle aktif
- Lihat console untuk error di `debounceSearch()`

## 📋 Debugging Checklist

Jika filter rusak, check hal-hal ini secara berurutan:

### 1. JavaScript Loading
- [ ] Buka Console, cek tidak ada error saat load page
- [ ] Cek file `assets/js/app.js` berhasil dimuat
- [ ] Lihat log initialization complete

### 2. Element Existence
- [ ] Semua dropdown trigger element ada
- [ ] Semua button filter ada
- [ ] Ship date button ada

### 3. Event Listeners
- [ ] Dropdown bisa dibuka
- [ ] Filter button bisa diklik dan toggle active
- [ ] Ship date button merespon klik

### 4. Functionality
- [ ] Pilih country dari dropdown works
- [ ] Filter status toggle works
- [ ] Search button trigger search
- [ ] Results muncul di table

## 🔍 Specific Error Messages

### "Cannot read property 'addEventListener' of null"
**Meaning:** Element tidak ditemukan di DOM
**Fix:** Check apakah ID element di HTML match dengan ID di JavaScript

### "setupCountryDropdown is not defined"
**Meaning:** Function tidak terdefinisi atau typo
**Fix:** Check function name spelling, pastikan app.js loaded

### "toggleShipDateCalendar is not defined"
**Meaning:** Function ship date calendar belum terdefinisi
**Fix:** Check apakah `setupShipDatePicker()` berhasil run

## 📞 Quick Diagnostic Commands

Paste ini di Console Browser untuk quick check:

```javascript
// Quick diagnostic
console.log('=== FILTER DIAGNOSTIC ===');
console.log('Auto-apply:', typeof autoApply !== 'undefined' ? autoApply : 'NOT DEFINED');
console.log('Selected carriers:', typeof selectedCarriers !== 'undefined' ? selectedCarriers : 'NOT DEFINED');
console.log('Selected statuses:', typeof selectedStatuses !== 'undefined' ? selectedStatuses : 'NOT DEFINED');

console.log('\n=== ELEMENTS CHECK ===');
const elements = {
    'country-dropdown-trigger': document.getElementById('country-dropdown-trigger'),
    'ship-date-trigger': document.getElementById('ship-date-trigger'),
    'dest-city-dropdown-trigger': document.getElementById('dest-city-dropdown-trigger'),
    'origin-country-dropdown-trigger': document.getElementById('origin-country-dropdown-trigger'),
};

Object.keys(elements).forEach(key => {
    console.log(`${key}:`, elements[key] !== null ? '✓ EXISTS' : '✗ MISSING');
});

console.log('\n=== FILTER BUTTONS ===');
console.log('Carrier buttons:', document.querySelectorAll('[data-type="carrier"]').length);
console.log('Status buttons:', document.querySelectorAll('[data-type="status"]').length);

console.log('\n=== FUNCTIONS CHECK ===');
const functions = [
    'setupFilterButtons',
    'setupCountryDropdown',
    'applyFilters',
    'performSearch',
    'toggleShipDateCalendar'
];

functions.forEach(fn => {
    console.log(`${fn}:`, typeof window[fn] !== 'undefined' || typeof eval(fn) !== 'undefined' ? '✓ DEFINED' : '✗ MISSING');
});
```

## 💡 Next Steps

1. **Upload semua file yang sudah dimodify:**
   - `assets/js/app.js` (dengan enhanced error handling)
   - `track.php` (dengan fixed ship date button)
   - `.htaccess` (dengan API exclusions)

2. **Clear cache:**
   - Hard refresh: Ctrl + Shift + R (Windows) atau Cmd + Shift + R (Mac)
   - Atau buka di Incognito/Private mode

3. **Test sistematis:**
   - Buka https://www.tukarkuy.web.id/track
   - Check console untuk initialization logs
   - Test setiap filter one by one
   - Note mana yang tidak work

4. **Jika masih error:**
   - Copy paste console error message
   - Run diagnostic command di atas
   - Buka test-track-filter.html untuk isolated test

---

**Update:** Juli 5, 2026
**Status:** Enhanced error handling & debugging added
