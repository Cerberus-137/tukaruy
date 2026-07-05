# Admin Panel - Complete Guide

## Overview
Admin panel for Tukarkuy allows you to:
- ✅ Manage ticket packages (harga, bonus, diskon)
- ✅ Enable/Disable payment methods (QRIS, Saweria)
- ✅ Update API tokens
- ✅ View dashboard statistics
- ✅ Manage transactions & users

## Access Admin Panel

**URL:** `https://tukaruy.online/admin`

**Default Admin Account:**
- Email: `admin@tukaruy.online`
- Password: `admin123` (change this immediately!)

## Features

### 1. Dashboard (`/admin`)
- **Total Users**: Jumlah pengguna terdaftar (role: user)
- **Total Revenue**: Total pendapatan dari pembayaran berhasil
- **Pending Payments**: Jumlah pembayaran yang menunggu konfirmasi
- **Credits Issued**: Total kredit yang terjual

### 2. Kelola Paket Harga (`/admin/packages`)

Kelola harga paket kredit Anda dengan mudah.

#### Cara Edit Harga:
1. Buka halaman "Paket Harga"
2. Setiap paket bisa di-edit langsung:
   - **Kredit**: Jumlah kredit (tidak bisa diubah)
   - **Harga (IDR)**: Harga paket
   - **Bonus**: Kredit bonus
   - **Total**: Bonus + Kredit
   - **Diskon %**: Diskon dalam persen
   - **Status**: Aktif/Nonaktif

3. Perubahan otomatis tersimpan
4. Paket nonaktif tidak akan ditampilkan ke pengguna

#### Contoh:
```
Paket: 10 Kredit
Harga: 500.000 IDR
Bonus: 1 Kredit
Total: 11 Kredit
Diskon: 10%
```

#### Tambah Paket Baru:
1. Klik tombol "+ Tambah Paket"
2. Isi field:
   - Kredit
   - Harga (IDR)
   - Bonus (opsional)
   - Diskon % (opsional)
3. Klik "Tambah"

#### Hapus Paket:
- Klik tombol "Hapus" di setiap paket (tidak bisa di-undo!)

### 3. Payment Methods (`/admin/payment-methods`)

Aktifkan atau matikan metode pembayaran.

#### Available Methods:
1. **QRIS Pay** (Default: Aktif)
   - Batas maksimal: Rp 499.000 per transaksi
   - Metode: QRIS code di e-wallet

2. **Saweria** (Default: Nonaktif)
   - Tidak ada batasan jumlah
   - Metode: Donasi melalui Saweria

#### Cara Aktifkan/Matikan:
1. Di halaman "Payment Methods", cari metode yang ingin diubah
2. Toggle switch ON/OFF
3. Perubahan otomatis tersimpan

#### Update API Tokens:
Jika kamu punya token baru:
1. Masukkan token di field yang tersedia
2. Klik tombol "Simpan"

### 4. Transaksi (`/admin/payments`)

Lihat semua transaksi pembayaran:
- Status: Pending / Paid / Expired / Cancelled
- User info
- Jumlah & kredit
- Waktu transaksi

### 5. Users (`/admin/users`)

Lihat & manage pengguna:
- Info pengguna
- Jumlah kredits
- History pembayaran
- Aksi manual (add/remove credits)

### 6. Pengaturan (`/admin/settings`)

Konfigurasi API dan settings lainnya:
- QRISPay API token
- Saweria API token
- TrackTaco API key
- Base price per credit

---

## Payment Flow Explanation

Alur ketika user membeli kredit:

```
1. User buka halaman Tickets (/tickets)
2. User pilih paket kredit (dari database ticket_packages)
3. User klik "Beli" - aplikasi membuat payment record di database
4. Payment record masuk ke table `payments` dengan status = 'pending'
5. User bayar di QRIS/Saweria
6. Sistem auto-check status payment setiap 3 detik
7. Saat payment dikonfirmasi "paid":
   - Payment status berubah jadi 'paid'
   - Kredit otomatis ditambah ke user account
   - User bisa langsung gunakan kredit untuk get tracking number

Database flow:
┌─────────────┐
│ User clicks │
│ buy package │
└──────┬──────┘
       │
       ▼
┌──────────────────────┐
│ Create payment record │──┐ Insert ke table `payments`
│ (status=pending)      │  │ dengan user_id & amount
└──────┬───────────────┘  │
       │                   │
       ▼                   │
┌─────────────────┐        │
│ User bayar QRIS │        │
└──────┬──────────┘        │
       │                   │
       ▼                   │
┌─────────────────────────┐│
│ API payment/check       ││
│ polling status setiap   ││
│ 3 detik                 ││
└──────┬──────────────────┘│
       │                   │
       ▼                   ▼
┌──────────────────┐   ┌─────────────┐
│ Payment Success? │   │ Update DB:  │
│ Status = "paid"  │───│ payments    │
└──────────────────┘   │ status=paid │
                       │             │
                       │ + tickets   │
                       │ to user     │
                       └─────────────┘
```

---

## Important Notes

### QRIS Payment Limit
- Maksimal Rp 499.000 per transaksi
- Paket di atas ini harus menggunakan Saweria (jika diaktifkan)
- Untuk membeli paket besar, bisa:
  1. Split jadi multiple transactions
  2. Gunakan Saweria (jika available)
  3. Contact admin untuk custom package

### Database Tables
Struktur database untuk payment system:

**ticket_packages** (Paket yang bisa dibeli)
```sql
- id INT (PK)
- credits INT (jumlah kredit dasar)
- price INT (harga dalam IDR)
- bonus INT (bonus kredit)
- total_credits INT (bonus + credits)
- discount_percentage INT (diskon %)
- active BOOLEAN (aktif/nonaktif)
- order_index INT (urutan di tampilan)
- updated_by INT (FK: users.id)
```

**payment_methods** (Metode pembayaran)
```sql
- id INT (PK)
- method_name ENUM ('qrispay', 'saweria')
- display_name VARCHAR
- enabled BOOLEAN
- icon VARCHAR
- sort_order INT
- updated_by INT (FK: users.id)
```

**payments** (Rekam transaksi)
```sql
- id INT (PK)
- user_id INT (FK: users.id)
- payment_method ENUM ('qrispay', 'saweria')
- qris_id VARCHAR (untuk QRIS)
- external_id VARCHAR (untuk Saweria)
- amount INT (harga)
- tickets INT (jumlah kredit dibeli)
- status ENUM ('pending','paid','expired','cancelled')
- paid_at TIMESTAMP (waktu pembayaran dikonfirmasi)
- created_at TIMESTAMP
```

---

## Troubleshooting

### Payment tidak bertambah otomatis?
1. Check di halaman "Transaksi" - cari payment user
2. Lihat status: Pending/Paid?
3. Jika status masih pending:
   - User belum bayar atau
   - Sistem belum terima konfirmasi dari API
4. Manual solution:
   - Go to "Users" page
   - Find user
   - Click "Add Credits" (jika tombol tersedia)
   - Input jumlah manual

### QRIS/Saweria error?
1. Cek API tokens di "Payment Methods"
2. Tokens sudah up-to-date?
3. Update tokens di panel jika perlu
4. Test dengan payment kecil dulu

### Paket tidak muncul di halaman Tickets?
1. Paket inactive?
2. Cek di "Paket Harga" - status harus "Aktif"
3. Atau cek code di `/tickets` - mungkin tidak load dari database

---

## API Endpoints (Untuk Developer)

### Stats
- `GET /admin/api/stats` - Get dashboard statistics

### Packages
- `POST /admin/api/packages/update` - Update paket field
- `POST /admin/api/packages/create` - Tambah paket baru
- `POST /admin/api/packages/delete` - Hapus paket

### Payment Methods
- `POST /admin/api/payment-methods/update` - Enable/disable metode

### Settings
- `POST /admin/api/settings/update` - Update API tokens

---

## Security

⚠️ **Important:**
- Ganti password admin default!
- Jaga API tokens - jangan share
- Limit admin access hanya untuk trusted people
- All admin actions are logged (table: admin_settings dengan updated_by)

---

## Next Steps

1. ✅ **Login ke admin panel** - `/admin`
2. ✅ **Update harga paket** - Sesuai pricing Anda
3. ✅ **Enable/Disable payment methods** - Sesuai kebutuhan
4. ✅ **Monitor dashboard** - Check revenue & transactions
5. ⏳ **Coming Soon**: User management, chat system

---

## Support

Untuk pertanyaan atau issues:
- Check error di browser console (F12)
- Check server logs
- Contact developer

---

*Last Updated: July 5, 2026*
