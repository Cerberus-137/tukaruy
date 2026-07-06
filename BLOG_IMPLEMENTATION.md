# 📝 Blog System Implementation Guide

## ✅ Files Created

### Database
- `database_blog.sql` - Database schema for blog and contact system

### Frontend Pages
- `blog.php` - Blog listing page with categories and search
- `blog-article.php` - Individual article detail page with related articles

### Admin Panel
- `admin/blog.php` - Blog management dashboard
- `admin/blog-create.php` - Create new article with TinyMCE editor
- `admin/blog-edit.php` - Edit existing articles
- `admin/contact.php` - Manage contact form submissions

### Assets
- `uploads/blog/` - Directory for blog images
- `uploads/.htaccess` - Protection for uploads directory

### Homepage
- `index.php` - REDESIGNED with better product showcase

---

## 🚀 Installation Steps

### 1. Import Database Schema
```bash
mysql -u root -p tukarkuy < database_blog.sql
```

Or via phpMyAdmin:
1. Open phpMyAdmin
2. Select `tukarkuy` database
3. Go to Import tab
4. Choose `database_blog.sql`
5. Click "Go"

### 2. Set Directory Permissions
```bash
# Windows (Run as Administrator)
icacls "a:\Bot\Website\traco\tukaruy\uploads" /grant Users:F /t

# Linux/Mac
chmod 755 uploads/
chmod 755 uploads/blog/
```

### 3. Update Navigation Links
Already updated in:
- `blog.php` - Links to homepage, contact
- `blog-article.php` - Back to blog link
- `admin/blog.php` - Admin navigation with blog tab
- `admin/contact.php` - Admin navigation with contact tab
- `index.php` - Links to blog and contact

---

## 📖 How to Use

### For Admins:

#### Create New Article
1. Login as admin
2. Go to `/admin/blog`
3. Click "Create New Article"
4. Fill in:
   - **Title** (required) - Auto-generates slug
   - **Slug** - URL-friendly identifier (auto-generated or custom)
   - **Excerpt** - Short description for cards/meta
   - **Content** - Full article content (TinyMCE editor)
   - **Category** - Select or create new
   - **Status** - Draft, Published, or Archived
   - **Tags** - Comma-separated keywords
   - **Featured Image** - Direct URL or upload to `/uploads/blog/`
5. Save as Draft or Publish

#### Upload Images
**Option 1: Direct URL**
- Use any image URL (e.g., Unsplash, Imgur)
- Paste in "Featured Image URL" field

**Option 2: Upload to Server**
1. Upload image via FTP/cPanel to `/uploads/blog/`
2. Use URL: `https://tukarkuy.web.id/uploads/blog/your-image.jpg`
3. Paste in "Featured Image URL" field

**Option 3: Use TinyMCE Image Plugin**
- In content editor, click Image icon
- Insert image URL or upload (if configured)

#### Manage Contact Submissions
1. Go to `/admin/contact`
2. View all submissions with status indicators
3. Actions:
   - Click subject to view full message
   - Mark as Read
   - Mark as Replied
   - Delete submission

### For Users:

#### Browse Blog
1. Visit `/blog`
2. Filter by category
3. Search articles
4. Click article to read full content

#### Contact Form
1. Visit `/contact`
2. Fill in name, email, subject, message
3. Complete CAPTCHA
4. Submit

---

## 🎨 Customization

### Change Blog Colors
Edit `blog.php` and `blog-article.php`:
```php
<!-- Purple/Blue gradient -->
from-purple-600 to-blue-600

<!-- Change to Red/Orange -->
from-red-600 to-orange-600
```

### Add More Categories
Default categories in database:
- General
- Tutorial
- News
- Tips & Tricks
- Case Study
- Updates

Add more via SQL:
```sql
INSERT INTO blog_categories (name, slug, description) VALUES
('Shipping Guide', 'shipping-guide', 'Guides for shipping'),
('Carrier Info', 'carrier-info', 'Carrier information');
```

### Modify Articles Per Page
Edit `blog.php` line 10:
```php
$perPage = 9; // Change to 12, 15, etc.
```

---

## 🔧 TinyMCE Editor Configuration

### Current Setup
- Free version (no API key required)
- Dark theme for admin panel
- Plugins: lists, links, images, code, tables
- Auto-saves drafts

### Upgrade to Premium (Optional)
1. Get API key from https://www.tiny.cloud/
2. Replace in `admin/blog-create.php` and `admin/blog-edit.php`:
```javascript
<script src="https://cdn.tiny.cloud/1/YOUR-API-KEY/tinymce/6/tinymce.min.js"></script>
```
3. Add premium plugins:
```javascript
plugins: [
    // ... existing plugins
    'powerpaste', 'advcode', 'checklist' // Premium plugins
],
```

---

## 🛡️ Security Notes

### Already Implemented:
- ✅ CSRF protection via session tokens
- ✅ XSS protection with `htmlspecialchars()`
- ✅ SQL injection protection with prepared statements
- ✅ Admin-only access for management pages
- ✅ Upload directory protected via .htaccess
- ✅ Rate limiting on contact form

### Recommendations:
1. **Enable CAPTCHA on contact form** (already implemented)
2. **Regular backups** of database and uploads folder
3. **Monitor uploads** for suspicious files
4. **Use HTTPS** in production

---

## 📊 Database Schema

### blog_articles
- `id` - Primary key
- `title` - Article title
- `slug` - URL-friendly identifier (unique)
- `excerpt` - Short description
- `content` - Full article HTML
- `featured_image` - Image URL
- `category` - Article category
- `tags` - Comma-separated tags
- `author_id` - Foreign key to users table
- `status` - draft, published, archived
- `views` - View counter
- `published_at` - Publication timestamp
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### contact_submissions
- `id` - Primary key
- `name` - Sender name
- `email` - Sender email
- `subject` - Message subject
- `message` - Message content
- `status` - new, read, replied, archived
- `ip_address` - Sender IP
- `user_agent` - Browser/device info
- `created_at` - Submission timestamp
- `updated_at` - Last update timestamp

---

## 🎯 SEO Optimization

### Already Implemented:
- ✅ Meta descriptions from excerpt
- ✅ Clean URLs with slugs
- ✅ Open Graph tags (article page)
- ✅ Structured data for articles

### Recommendations:
1. **Add sitemap.xml** with all blog posts
2. **Submit to Google Search Console**
3. **Use descriptive image alt tags**
4. **Internal linking** between related articles
5. **Social sharing buttons**

---

## 📱 Responsive Design

All pages are fully responsive:
- Mobile: Single column layout
- Tablet: 2 columns for articles
- Desktop: 3 columns + sidebar

Tested on:
- iPhone (iOS)
- Android phones
- iPad/tablets
- Desktop (1920x1080)

---

## 🐛 Troubleshooting

### Issue: Articles not showing
**Solution:** Check if articles are published
```sql
SELECT * FROM blog_articles WHERE status = 'published';
```

### Issue: Images not loading
**Solution:** Check file permissions and URL
```bash
# Check if file exists
ls -la uploads/blog/your-image.jpg

# Check URL in browser
https://tukarkuy.web.id/uploads/blog/your-image.jpg
```

### Issue: TinyMCE not loading
**Solution:** Check browser console for errors
- Make sure CDN is accessible
- Check for JavaScript errors
- Try disabling browser extensions

### Issue: Contact form not saving
**Solution:** Check database connection
```php
// In contact.php, add debug line
error_log('Contact form submission: ' . print_r($_POST, true));
```

---

## 📞 Support

If you encounter issues:
1. Check error logs: `logs/php-errors.log`
2. Check Apache logs: `/var/log/apache2/error.log`
3. Enable debug mode temporarily (see config.php)
4. Contact support: support@tukarkuy.web.id

---

## 🎉 What's Next?

### Future Enhancements:
- [ ] Comments system for articles
- [ ] Article likes/reactions
- [ ] Email notifications for new articles
- [ ] RSS feed for blog
- [ ] Social media sharing buttons
- [ ] Author profiles
- [ ] Article scheduling
- [ ] Draft preview feature
- [ ] Bulk actions for articles
- [ ] Analytics dashboard

---

**Last Updated:** July 6, 2026  
**Version:** 1.0.0  
**Author:** Kiro AI Assistant
