# Tukeruy - Shipment Tracking Dashboard

Modern, clean, and premium SaaS interface for tracking shipments across multiple carriers (FedEx, DHL, UPS).

## Features

- 🎨 Modern dark theme with glass-morphism effects
- 🔍 Advanced filtering and search capabilities
- 📊 Real-time tracking statistics
- 🚀 Fast and responsive design
- 💳 Credit-based tracking number reveal system
- 📱 Fully responsive (Desktop, Tablet, Mobile)

## Tech Stack

- **Backend:** PHP 7.4+
- **Frontend:** HTML5, Tailwind CSS, Vanilla JavaScript
- **API:** TrackTaco API v2
- **Server:** Apache with mod_rewrite

## Requirements

- PHP 7.4 or higher
- Apache 2.4+
- curl extension enabled
- mod_rewrite enabled
- SSL certificate (for production)

## Installation on Ubuntu 22.04

### 1. Connect to Your Server

```bash
ssh root@178.83.181.62
```

### 2. Update System

```bash
apt update && apt upgrade -y
```

### 3. Install Apache, PHP, and Required Extensions

```bash
apt install apache2 php libapache2-mod-php php-curl php-json php-mbstring -y
```

### 4. Enable Required Apache Modules

```bash
a2enmod rewrite
a2enmod headers
systemctl restart apache2
```

### 5. Create Web Directory

```bash
mkdir -p /var/www/tukeruy
cd /var/www/tukeruy
```

### 6. Upload Project Files

You can use SCP, SFTP, or git to upload the project files:

```bash
# Using git (if you have a repository)
git clone https://github.com/Cerberus-137/tukaruy /var/www/tukeruy

# Or use SCP from your local machine:
# scp -r /path/to/tukeruy/* root@178.83.181.62:/var/www/tukeruy/
```

### 7. Set Permissions

```bash
chown -R www-data:www-data /var/www/tukeruy
chmod -R 755 /var/www/tukeruy
```

### 8. Configure Apache Virtual Host

Create a new virtual host configuration:

```bash
nano /etc/apache2/sites-available/tukeruy.conf
```

Add the following configuration:

```apache
<VirtualHost *:80>
    ServerName tukeruy.com
    ServerAlias www.tukeruy.com
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

Save and exit (Ctrl+X, then Y, then Enter).

### 9. Enable the Site

```bash
a2dissite 000-default.conf
a2ensite tukeruy.conf
systemctl reload apache2
```

### 10. Configure the Application

Edit the configuration file:

```bash
nano /var/www/tukeruy/config.php
```

Update the following line with your API key:

```php
define('API_KEY', 'tt_live_YOUR_ACTUAL_API_KEY_HERE');
```

Save and exit.

### 11. Install SSL Certificate (Recommended for Production)

```bash
apt install certbot python3-certbot-apache -y
certbot --apache -d tukaruy.online -d www.tukaruy.online
```

Follow the prompts to complete SSL installation.

### 12. Configure Firewall (if using UFW)

```bash
ufw allow 'Apache Full'
ufw enable
```

### 13. Test the Installation

Open your browser and navigate to:
- HTTP: `http://178.83.181.62` or `http://tukeruy.com`
- HTTPS: `https://tukeruy.com` (after SSL installation)

## Configuration

### API Key Setup

1. Sign up for a TrackTaco account at https://tracktaco.com
2. Get your API key from the dashboard
3. Update `config.php` with your API key:

```php
define('API_KEY', 'tt_live_your_api_key_here');
```

### Customization

You can customize various aspects of the application by editing:

- `config.php` - Application settings
- `index.php` - Main page layout
- `assets/js/app.js` - Frontend functionality
- CSS in `<style>` tag in `index.php` - Styling

## Usage

### Basic Search

1. Use the filter sidebar to select carriers, status, and other criteria
2. Click "Apply Filters" to search
3. Results will appear in the main table

### Revealing Tracking Numbers

1. Click "Get TN" button on any result
2. Confirm the reveal (costs 1 credit)
3. The tracking number will be displayed

### Advanced Filters

- **Carrier:** Filter by FedEx, DHL, or UPS
- **Status:** Pre-Transit, Transit, or Delivered
- **Destination:** Filter by country and city
- **Date Range:** Filter by estimated delivery date
- **Advanced Options:** Signature required, Photo on delivery

## API Endpoints

The application provides the following internal API endpoints:

- `POST /api/search.php` - Search tracking numbers
- `POST /api/reveal.php` - Reveal a tracking number
- `GET /api/account.php` - Get account information and history

## Troubleshooting

### Apache won't start
```bash
systemctl status apache2
# Check error logs
tail -f /var/log/apache2/error.log
```

### Permission errors
```bash
chown -R www-data:www-data /var/www/tukeruy
chmod -R 755 /var/www/tukeruy
```

### .htaccess not working
```bash
# Make sure mod_rewrite is enabled
a2enmod rewrite
systemctl restart apache2
```

### API errors
- Check your API key in `config.php`
- Verify your TrackTaco account has credits
- Check error logs: `tail -f /var/log/apache2/tukeruy_error.log`

## Security Recommendations

1. **Change default API key** in `config.php`
2. **Use HTTPS** in production (SSL certificate)
3. **Restrict access** to `config.php` (already done in `.htaccess`)
4. **Regular updates:** Keep PHP and Apache updated
5. **Monitor logs:** Regularly check Apache error logs
6. **Firewall:** Use UFW or iptables to restrict access

## Maintenance

### Update the application
```bash
cd /var/www/tukeruy
git pull origin main  # If using git
systemctl reload apache2
```

### Check logs
```bash
# Application logs
tail -f /var/log/apache2/tukeruy_error.log

# Access logs
tail -f /var/log/apache2/tukeruy_access.log
```

## License

This project is proprietary software.

## Support

For issues or questions, please contact your system administrator.

## Credits

Built with modern web technologies and inspired by Linear, Stripe Dashboard, Vercel, and Notion design principles.
