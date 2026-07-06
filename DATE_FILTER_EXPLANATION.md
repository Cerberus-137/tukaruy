# Date Filter Explanation

## Perbedaan 2 Filter Tanggal

Ada 2 filter tanggal yang berbeda dan independen di sistem tracking:

### 1. SHIP DATE WINDOW (Tanggal Pengiriman)
**Icon**: 📦 Calendar (Purple)
**API Field**: `shipped_between.from` dan `shipped_between.to`
**Fungsi**: Filter berdasarkan kapan **paket dikirim** dari pengirim

**Contoh Penggunaan**:
- Jika set: 25 Juni - 30 Juni
- Sistem akan mencari semua paket yang **dikirim (shipped)** antara tanggal 25-30 Juni
- **Tidak peduli** kapan estimasi tiba nya

**Use Case**:
- "Saya ingin lihat paket yang dikirim minggu ini"
- "Tampilkan semua paket yang sudah dikirim bulan lalu"

---

### 2. EST. DELIVERY WINDOW (Estimasi Tanggal Tiba)
**Icon**: ⏰ Clock (Blue)
**API Field**: `est_delivery_between.from` dan `est_delivery_between.to`
**Fungsi**: Filter berdasarkan kapan **paket diperkirakan tiba** di tujuan

**Contoh Penggunaan**:
- Jika set: 27 Juni - 30 Juni
- Sistem akan mencari semua paket yang **diperkirakan tiba** antara tanggal 27-30 Juni
- **Tidak peduli** kapan paket dikirim
- Hasil bisa menampilkan paket yang dikirim tanggal 18-30 Juni (selama estimasi tiba nya 27-30 Juni)

**Use Case**:
- "Saya ingin lihat paket yang akan tiba minggu depan"
- "Tampilkan paket yang harus tiba sebelum tanggal 30"

---

## Cara Kerja di API

### API Request Structure:
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

### Filter Independent:
- **Bisa digunakan bersamaan**: Cari paket yang dikirim 25-30 Juni DAN estimasi tiba 27-30 Juni
- **Bisa digunakan sendiri-sendiri**: 
  - Hanya ship date saja
  - Hanya delivery date saja
  - Atau keduanya kosong (no date filter)

---

## UI Flow

### SHIP DATE WINDOW:
1. User klik button "Select date range..."
2. Calendar modal muncul (atau prompt input)
3. User pilih range: 25 Juni - 30 Juni
4. Display berubah: "Jun 25, 2026 - Jun 30, 2026"
5. Hidden input `ship_from` dan `ship_to` terisi
6. Auto-apply filter (jika enabled)

### EST. DELIVERY WINDOW:
1. User klik button "Select date range..."
2. Calendar modal muncul (atau prompt input)
3. User pilih range: 27 Juni - 30 Juni
4. Display berubah: "Jun 27, 2026 - Jun 30, 2026"
5. Hidden input `delivery_from` dan `delivery_to` terisi
6. Auto-apply filter (jika enabled)

---

## Catatan Penting

1. **Pre-transit labels**: Paket yang belum dikirim (pre-transit) **tidak memiliki estimasi delivery**, jadi filter EST. DELIVERY WINDOW tidak akan menampilkan paket pre-transit.

2. **Independent Filters**: Kedua filter bekerja INDEPENDENT, artinya:
   - Ship date 25-30 Juni saja → Tampilkan semua paket dikirim 25-30 (apapun estimasi tiba nya)
   - Delivery date 27-30 Juni saja → Tampilkan semua paket estimasi tiba 27-30 (apapun tanggal kirim nya)
   - Keduanya aktif → Tampilkan paket yang dikirim 25-30 DAN estimasi tiba 27-30

3. **Date Format**: Semua tanggal menggunakan format `YYYY-MM-DD` (ISO 8601) untuk compatibility dengan API.

---

## Testing Checklist

- [ ] Ship date filter bekerja sendiri
- [ ] Delivery date filter bekerja sendiri  
- [ ] Kedua filter bekerja bersamaan
- [ ] Clear button untuk ship date
- [ ] Clear button untuk delivery date
- [ ] Display text update dengan benar
- [ ] Auto-apply filter jika enabled
- [ ] Notification muncul saat apply filter
- [ ] Reset filter menghapus kedua date filter

---

**Dibuat**: 6 Juli 2026
**Versi**: 1.0
