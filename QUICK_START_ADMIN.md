# Admin Panel - Quick Start Guide

## 🚀 Getting Started (5 Minutes)

### Step 1: Login to Admin Panel
```
URL: https://tukaruy.online/admin
Email: admin@tukaruy.online
Password: admin123
```

⚠️ **First thing:** Change your password!

### Step 2: Update Ticket Prices

1. Click **"Paket Harga"** in sidebar
2. Edit prices directly in the table:
   - Change **Harga (IDR)** column
   - Changes save automatically
3. Test: Go to `/tickets`, verify prices updated

**Example:**
```
Paket 10 Kredit
Harga Lama: 500.000
Harga Baru: 450.000 ← Edit this
✅ Save otomatis
```

### Step 3: Enable/Disable Payment Methods

1. Click **"Payment Method"** in sidebar
2. Toggle switches:
   - ✅ QRIS Pay (default: ON)
   - ☐ Saweria (default: OFF)
3. Test: Go to `/tickets`, verify payment options changed

### Step 4: Monitor Dashboard

Click **"Dashboard"** to see:
- 👥 Total Users
- 💰 Total Revenue (Rp)
- ⏳ Pending Payments
- 🎫 Credits Issued

---

## 📋 Complete Admin Workflow

```
┌─────────────────────────────────────┐
│     Admin Panel Dashboard            │
├─────────────────────────────────────┤
│ • Paket Harga (Manage Prices)       │
│   └─ Edit price/bonus/discount      │
│                                     │
│ • Payment Methods (ON/OFF Toggle)   │
│   └─ QRIS, Saweria                  │
│                                     │
│ • Users (View All)                  │
│   └─ See credits balance            │
│                                     │
│ • Transaksi (All Payments)          │
│   └─ Track pending/paid             │
│                                     │
│ • Dashboard (Stats)                 │
│   └─ Revenue, users, credits        │
└─────────────────────────────────────┘
```

---

## 💡 Common Tasks

### Change Price for 1 Package
```
1. Go: Paket Harga
2. Find: Package row
3. Click: Harga field
4. Type: New price (e.g., 450000)
5. ✅ Auto-save (no button needed)
6. Test: /tickets page
```

### Add New Package
```
1. Go: Paket Harga
2. Click: "+ Tambah Paket" button
3. Fill: Credits, Harga, Bonus, Diskon%
4. Click: "Tambah"
5. ✅ Package added & visible on /tickets
```

### Disable Saweria (keep only QRIS)
```
1. Go: Payment Methods
2. Find: Saweria card
3. Toggle: OFF (switch to left)
4. ✅ Saweria removed from checkout
5. Test: /tickets - only QRIS available
```

### Enable Saweria (for big packages)
```
1. Go: Payment Methods
2. Find: Saweria card
3. Toggle: ON (switch to right)
4. ✅ Saweria now available
5. Note: Good for packages > 499k (QRIS limit)
```

---

## 🔧 Settings & API

### Update API Tokens (if needed)

Go: Payment Methods → API Configuration

```
1. QRISPay Token
   - Field: "QRISPay API Token"
   - Paste: New token (cki_...)
   - Click: Simpan

2. Saweria Token
   - Field: "Saweria API Token"
   - Paste: New token (eyJ...)
   - Click: Simpan

3. TrackTaco API Key
   - Field: Under Settings page (future)
   - Current: tt_live_...
```

---

## 📊 Dashboard Stats Explained

| Stat | What It Means | Example |
|------|---|---|
| **Total Users** | Registered users (role=user) | 42 users |
| **Total Revenue** | Sum of all paid payments | Rp 5.000.000 |
| **Pending Payments** | Waiting for user confirmation | 3 pending |
| **Credits Issued** | Total credits sold (paid orders) | 1.250 credits |

---

## 🆘 Troubleshooting

### Problem: Can't login to admin panel
```
✓ Email correct? admin@tukaruy.online
✓ Password correct? admin123 (default)
✓ User role is 'admin'? Check DB
```

### Problem: Price changes don't show on /tickets
```
✓ Reload /tickets page (CTRL+F5)
✓ Clear browser cache
✓ Check: Package is active? (Status column)
✓ Database updated? Check admin_settings table
```

### Problem: Payment method toggle not working
```
✓ Check: Database updated? Select * from payment_methods
✓ Reload: /tickets page
✓ Check: Browser console for JS errors (F12)
```

### Problem: QRIS payment stuck at pending
```
✓ User actually paid?
✓ Check QRIS status with /admin/payments page
✓ Try manual check: php cli/check-payments.php
✓ Check payment API token valid?
```

---

## 🔐 Security Notes

### Do These:
- ✅ Change default password immediately
- ✅ Use strong password
- ✅ Keep API tokens secret
- ✅ Monitor who has admin access

### Don't Do These:
- ❌ Share admin login credentials
- ❌ Paste API tokens in chat/email
- ❌ Leave admin panel public
- ❌ Delete active packages without backup

---

## 📞 Quick Reference

| Action | Path | Time |
|--------|------|------|
| Change Price | /admin/packages | 30 sec |
| Toggle Payment Method | /admin/payment-methods | 20 sec |
| View Users | /admin/users | 1 min |
| Check Revenue | /admin (dashboard) | 1 min |
| Check Pending Payments | /admin/payments | 2 min |

---

## 🎯 What Happens After I Change a Price?

```
You edit price in admin panel
        ↓
Database updates immediately
        ↓
User visits /tickets page
        ↓
New price loads from database
        ↓
User sees updated price
        ↓
User buys at new price
        ↓
Old price orders NOT affected
```

**Key:** Only NEW orders use new prices. Past orders keep original prices.

---

## ✅ First-Time Setup Checklist

- [ ] Login to admin panel
- [ ] Change default password
- [ ] Set correct prices for all packages
- [ ] Enable/disable payment methods
- [ ] Test QRIS by making small purchase
- [ ] Verify payment auto-adds credits
- [ ] Check dashboard stats update
- [ ] Invite team members (future)

---

## 📱 Mobile Access

Admin panel works on mobile, but:
- Better on desktop for table editing
- Use Chrome/Safari browser
- Tap fields to edit
- Swipe left/right for full table view

---

## 🚨 Emergencies

### If Payment System Down:
```
1. Go: /admin/payment-methods
2. Check: Is QRIS enabled?
3. Check: API token valid?
4. Disable QRIS, enable Saweria as backup
5. OR: Wait for developer to fix
```

### If Prices Wrong:
```
1. Go: /admin/packages
2. Fix: Edit price field
3. Verify: /tickets shows correct price
4. Notify: Users if overpaid
```

### If User Lost Credits:
```
1. Go: /admin/users
2. Find: User by email
3. See: Current balance
4. Manual Fix: (feature coming soon)
5. OR: Contact developer
```

---

## 📚 Documentation

- **Full Guide:** `ADMIN_PANEL_GUIDE.md`
- **Technical Details:** `IMPLEMENTATION_SUMMARY.md`
- **Database Schema:** `database.sql`

---

## 🎉 You're Ready!

Your admin panel is now live. Start managing:
1. 💰 Prices
2. 🔌 Payment methods
3. 👥 Users & credits
4. 📊 Revenue & stats

**Questions?** Check the full guide: `ADMIN_PANEL_GUIDE.md`

---

*Last Updated: July 5, 2026*  
*Version: 1.0 - Production Ready*
