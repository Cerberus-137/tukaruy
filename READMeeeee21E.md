# Tukeruy - Tracking Number Platform

Modern SaaS platform for obtaining tracking numbers across multiple carriers (FedEx, DHL, UPS) with integrated payment system.

## 🚀 Features

### Core Features
- 🎨 Modern dark theme with glass-morphism effects
- 👤 User authentication & registration system
- 🔍 **Advanced filtering system**:
  - 60+ countries supported
  - 257+ cities with search
  - US state selection (50 states)
  - ZIP code filtering
  - Date range filters (shipping & delivery)
  - Auto-apply filters with 800ms debounce
- 🎫 Credit-based tracking reveal system
- 📊 Real-time statistics dashboard (async loading)
- 📱 Fully responsive design

### Payment System
- 💳 **Dual payment methods**:
  - QRIS (QRISPay) - Instant QRIS code generation
  - Saweria - Donation-based payment
- 💰 Multiple ticket packages with bonus credits
- 🔄 Real-time payment status checking
- ✅ Automatic credit addition after payment

### Admin Features
- 🔧 Web-based API key management
- 🎛️ Payment method toggles
- 📈 Transaction monitoring
- 👥 User management
- 🔑 Secure settings interface

### User Experience
- ⚡ Fast page load (< 1 second)
- 🔄 Auto-refresh statistics
- 📜 Reveal history modal
- 🔔 Real-time notifications
- 🎯 Smart search with filters

## � Pricing & Packages

| Package | Price | Base Credits | Bonus | Total | Savings |
|---------|-------|--------------|-------|-------|---------|
| Starter | Rp 250,000 | 5 | +2 | 7 | - |
| Popular | Rp 500,000 | 10 | +4 | 14 | 40% |
| Pro | Rp 1,250,000 | 25 | +10 | 35 | 40% |
| Business | Rp 2,500,000 | 50 | +20 | 70 | 40% |
| Enterprise | Rp 5,000,000 | 100 | +50 | 150 | 50% |

**Base Price:** Rp 50,000 per credit

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+
- **Database:** MariaDB 10.6+
- **Frontend:** HTML5, Tailwind CSS, Vanilla JavaScript
- **APIs:** 
  - TrackTaco API v2 (tracking data)
  - QRISPay API (payment gateway)
- **Server:** Apache 2.4+ with mod_rewrite

## 📋 Requirements

- Ubuntu 22.04 LTS (recommended)
- PHP 7.4 or higher
- MariaDB 10.6 or higher
- Apache 2.4+
- curl, json, mbstring, pdo_mysql extensions
- SSL certificate (for production)

## 🔧 Installation on Ubuntu 22.04

### Step 1: Connect to Server

```bash
ssh root@YOUR_SERVER_IP
```

### Step 2: Update System

```bash
apt update && apt upgrade -y
```

### Step 3: Install Apache & PHP

```bash
apt install apache2 -y
apt install php php-cli php-fpm php-mysql php-curl php-json php-mbstring php-xml php-zip -y
```

### Step 4: Install MariaDB

```bash
# Install MariaDB server
apt install mariadb-server mariadb-client -y

# Secure MariaDB installation
mysql_secure_installation
```

**During `mysql_secure_installation`, answer:**
- Enter current password: `(press Enter for none)`
- Switch to unix_socket authentication: `N`
- Change root password: `Y` → enter your password
- Remove anonymous users: `Y`
- Disallow root login remotely: `Y`
- Remove test database: `Y`
- Reload privilege tables: `Y`

### Step 5: Create Database

```bash
# Login to MariaDB
mysql -u root -p

# Create database and user
CREATE DATABASE tukeruy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tukeruy_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';
GRANT ALL PRIVILEGES ON tukeruy.* TO 'tukeruy_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 6: Enable Apache Modules

```bash
a2enmod rewrite
a2enmod headers
a2enmod ssl
systemctl restart apache2
```

### Step 7: Create Web Directory

```bash
mkdir -p /var/www/tukeruy
cd /var/www/tukeruy
```

### Step 8: Upload Project Files

**Option A: Using Git**
```bash
git clone https://github.com/YOUR_USERNAME/tukeruy.git /var/www/tukeruy
```

**Option B: Using SCP from your local machine**
```bash
# Run this from your local machine (Windows)
scp -r c:\Users\Win-10\Documents\Bot\Resi\* root@YOUR_SERVER_IP:/var/www/tukeruy/
```

**Option C: Using WinSCP or FileZilla**
- Connect to your server
- Upload all files to `/var/www/tukeruy/`

### Step 9: Import Database Schema

```bash
cd /var/www/tukeruy
mysql -u root -p tukeruy < database.sql
```

**Alternative: Run Migration Script**

If you already have an existing database, run the migration script to update schema:

```bash
# Via browser
https://tukaruy.online/migrate.php

# Or via CLI
php migrate.php
```

The migration script will:
- Add new columns for dual payment system
- Create indexes for better performance
- Update admin_settings table
- Safe to run multiple times (idempotent)

### Step 10: Configure Application

```bash
nano /var/www/tukeruy/config.php
```

Update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tukeruy');
define('DB_USER', 'tukeruy_user');
define('DB_PASS', 'YOUR_SECURE_PASSWORD');

define('SITE_URL', 'https://tukaruy.online');
```

### Step 11: Set Permissions

```bash
chown -R www-data:www-data /var/www/tukeruy
chmod -R 755 /var/www/tukeruy
chmod -R 775 /var/www/tukeruy/api
```

### Step 12: Configure Apache Virtual Host

```bash
nano /etc/apache2/sites-available/tukeruy.conf
```

Add this configuration:

```apache
<VirtualHost *:80>
    ServerName tukaruy.online
    ServerAlias www.tukaruy.online
    DocumentRoot /var/www/tukeruy

    <Directory /var/www/tukeruy>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tukeruy_error.log
    CustomLog ${APACHE_LOG_DIR}/tukeruy_access.log combined
</VirtualHost>
```

Save (Ctrl+X, Y, Enter).

### Step 13: Enable Site

```bash
a2dissite 000-default.conf
a2ensite tukeruy.conf
systemctl reload apache2
```

### Step 14: Install SSL Certificate

```bash
apt install certbot python3-certbot-apache -y
certbot --apache -d tukaruy.online -d www.tukaruy.online
```

Follow prompts and choose to redirect HTTP to HTTPS.

### Step 15: Configure Firewall

```bash
ufw allow 'Apache Full'
ufw allow OpenSSH
ufw enable
```

### Step 16: Test Installation

Open browser: `https://tukaruy.online`

**Default Admin Login:**
- Email: `admin@tukaruy.online`
- Password: `admin123`

**⚠️ IMPORTANT:** Change admin password immediately after first login!

## 🎯 Post-Installation

### 1. Update Admin Password

Login → Settings → Change Password

### 2. Configure API Keys (Admin Panel)

Login as admin → Settings → API Configuration:

**Required API Keys:**
- **TrackTaco API Key**: For tracking data access
  - Get from: https://tracktaco.com/dashboard
  - Format: `tt_live_xxxxxxxxxxxxx`
  
- **QRISPay API Token**: For QRIS payment (optional)
  - Get from: https://qrispy.id/dashboard
  - Format: `x-api-key: xxxxxxxxxxxxx`
  - Enable/disable via toggle in settings
  
- **Saweria API Token**: For Saweria payment (optional)
  - Get from: https://saweria.co/settings
  - Format: JWT token
  - Enable/disable via toggle in settings

**Important Notes:**
- At least one payment method must be enabled
- Both payment methods can be enabled simultaneously
- Users will see payment method selection if both are active

### 3. Test Payment System

**Test QRIS Payment (QRISPay):**
1. Create a test user account
2. Go to "Buy Tickets"
3. Select "QRIS" as payment method
4. Choose a package
5. Scan QRIS code with mobile banking
6. Verify credits are added automatically

**Test Saweria Payment:**
1. Create a test user account
2. Go to "Buy Tickets"
3. Select "Saweria" as payment method
4. Choose a package
5. Complete donation on Saweria page
6. Return to site (auto-redirect after payment)
7. Verify credits are added

**Important:**
- Test with small amounts first (Starter package)
- Keep payment receipts for reconciliation
- Monitor payment logs in admin panel

### 4. Monitor Logs

```bash
# Apache error logs
tail -f /var/log/apache2/tukeruy_error.log

# Apache access logs
tail -f /var/log/apache2/tukeruy_access.log

# MariaDB logs
tail -f /var/log/mysql/error.log
```

## 📁 Project Structure

```
tukeruy/
├── api/
│   ├── payment/
│   │   ├── create.php       # Create payment (QRIS/Saweria)
│   │   └── check.php        # Check payment status
│   ├── QRISPayAPI.php       # QRISPay integration class
│   ├── SaweriaAPI.php       # Saweria integration class (NEW)
│   ├── TukeruyAPI.php       # TrackTaco integration class
│   ├── search.php           # Advanced search with filters (NEW)
│   ├── reveal.php           # Reveal tracking number (NEW)
│   ├── account.php          # Account & history management (NEW)
│   └── stats.php            # Statistics endpoint (async)
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── admin/                   # Admin panel (TODO)
├── auth.php                 # Authentication functions
├── config.php               # Configuration
├── database.sql             # Database schema
├── migrate.php              # Database migration script (NEW)
├── index.php                # Landing page
├── login.php                # Login page
├── register.php             # Register page
├── logout.php               # Logout handler
├── track.php                # Enhanced tracking dashboard
├── tickets.php              # Buy tickets (dual payment)
├── settings.php             # User & admin settings
├── CHANGELOG.md             # Version history (NEW)
├── TROUBLESHOOTING.md       # Troubleshooting guide (NEW)
└── README.md                # This file
```

## 🔐 Security

### Production Checklist

- [ ] Change default admin password
- [ ] Update all API keys in database
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set `display_errors = 0` in config.php
- [ ] Configure strong database password
- [ ] Enable firewall (UFW)
- [ ] Regular backups
- [ ] Monitor error logs
- [ ] Update PHP and MariaDB regularly

### Database Backup

```bash
# Create backup
mysqldump -u root -p tukeruy > backup_$(date +%Y%m%d).sql

# Restore from backup
mysql -u root -p tukeruy < backup_YYYYMMDD.sql
```

## 🎫 Credit System

- Each credit = 1 tracking number reveal
- Credits never expire
- Bonus credits on larger packages
- Secure payment via QRIS

## 💳 Payment Integration

The platform supports **two payment methods**, providing flexibility for users:

### Payment Method 1: QRISPay (Recommended)

**Provider:** https://qrispy.id

**Features:**
- ⚡ Instant QRIS code generation
- 🔄 Real-time payment verification
- 💳 All Indonesian e-wallets & mobile banking
- ✅ Auto-credit after payment

**Flow:**
1. User selects credit package
2. System generates QRIS code via API
3. User scans with mobile banking app
4. System checks payment status every 3 seconds
5. Credits added automatically after confirmation

### Payment Method 2: Saweria

**Provider:** https://saweria.co

**Features:**
- 💝 Donation-based payment system
- 🌐 External payment page
- 🔗 Redirect-based flow
- ✅ Manual credit addition after verification

**Flow:**
1. User selects credit package
2. System creates donation link via API
3. User redirected to Saweria payment page
4. After payment, user returns to site
5. System verifies and adds credits

### Payment Configuration

Admin can enable/disable payment methods via Settings:
```sql
-- Check current payment method status
SELECT * FROM admin_settings 
WHERE setting_key IN ('qrispay_enabled', 'saweria_enabled');

-- Enable/disable via admin UI
Settings → API Configuration → Toggle switches
```

**Best Practice:**
- Enable both for maximum flexibility
- QRISPay for instant payments
- Saweria as alternative option
- At least one method must be active

## 👥 User Roles

### Regular User
- View own tickets balance
- Search & reveal tracking numbers
- Purchase ticket packages
- Update profile & password

### Admin
- All user permissions
- View TrackTaco API credits
- Update API keys (TrackTaco & QRISPay)
- View all transactions
- Manage user accounts

## 🔧 Troubleshooting

### Database Connection Failed

```bash
# Check MariaDB status
systemctl status mariadb

# Restart MariaDB
systemctl restart mariadb

# Check credentials in config.php
```

### Apache Won't Start

```bash
# Check Apache status
systemctl status apache2

# Check configuration
apache2ctl configtest

# View error logs
tail -f /var/log/apache2/error.log
```

### Payment Not Working

1. Check QRISPay API token in Admin Panel
2. Verify database connection
3. Check `/api/payment/` permissions
4. Review error logs

### Tracking Search Fails

1. Verify TrackTaco API key
2. Check API rate limits
3. Review `/api/search.php` logs

## 📊 Database Tables

- `users` - User accounts
- `payments` - Payment transactions
- `ticket_usage` - Ticket usage history
- `admin_settings` - System settings

## 🆘 Support

For issues or questions:
1. Check error logs
2. Review this documentation
3. Contact system administrator

## 📄 License

Proprietary software. All rights reserved.

## 🙏 Credits

- **TrackTaco API**: Tracking data provider
- **QRISPay**: Payment gateway
- **Design**: Inspired by modern SaaS platforms

---

**Built with ❤️ for efficient package tracking**

Last Updated: January 2026
