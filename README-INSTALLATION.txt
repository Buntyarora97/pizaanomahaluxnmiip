# MAHALUXMI TRADING HOUSE PVT. LTD. — E-Commerce Platform
PHP 8 + MySQL · Shared Hosting / cPanel Ready · No Node.js, No Composer

============================================================
01. REQUIREMENTS
============================================================
- PHP 8.0+ (works on 8.1/8.2/8.3)
- MySQL 5.7+ or MariaDB 10.4+
- Apache with mod_rewrite (.htaccess)
- PHP extensions: pdo_mysql, curl, mbstring, openssl, gd

============================================================
02. INSTALLATION (cPanel — 5 minutes)
============================================================
1. cPanel → MySQL® Databases → create:
   - Database:  cpuser_mthdb
   - User:      cpuser_mthuser (strong password)
   - Add user to database with ALL PRIVILEGES
2. cPanel → phpMyAdmin → select the database → Import → database.sql
3. cPanel → File Manager → open public_html → upload ALL project files
   (or upload the zip and Extract)
4. Edit /includes/config.php:
     DB_HOST = 'localhost'
     DB_NAME = 'cpuser_mthdb'
     DB_USER = 'cpuser_mthuser'
     DB_PASS = 'your password'
     SITE_URL = 'https://yourdomain.com'
5. Visit https://yourdomain.com — site is live.

============================================================
03. ADMIN ACCESS (CHANGE PASSWORD IMMEDIATELY)
============================================================
URL:     https://yourdomain.com/admin
Email:   admin@mahaluxmi.in
Pass:    Admin@123

To change: run this once in phpMyAdmin SQL (replace NEWPASS):
  UPDATE admins SET password = 'RESULT_OF_PHP_PASSWORD_HASH' WHERE email='admin@mahaluxmi.in';
Generate hash: create a temp file with <?php echo password_hash('YourNewPass', PASSWORD_DEFAULT);
open it once, copy output, then DELETE the file.

============================================================
04. RAZORPAY PAYMENT SETUP
============================================================
1. razorpay.com → Get API Keys (Live mode for production)
2. Admin → Settings → paste Key ID + Key Secret
3. Razorpay Dashboard → Settings → Webhooks → Add:
   URL:    https://yourdomain.com/payment/callback.php
   Secret: same as Key Secret
   Event:  payment.captured
4. Orders paid online are auto-verified server-side (HMAC signature).
   COD + Bank Transfer work with zero configuration.

============================================================
05. EMAIL SETUP
============================================================
Shared-hosting PHP mail() works out of the box on most hosts.
For SMTP: Admin → Settings → SMTP (use mail.yourdomain.com:587,
your cPanel mailbox credentials). From Email must exist on the server.

============================================================
06. POST-INSTALL CHECKLIST
============================================================
[ ] Change admin password
[ ] Admin → Settings: add GSTIN (only if verified), WhatsApp number,
    Google Maps embed URL
[ ] Admin → Categories: review/add categories & subcategories
[ ] Admin → Brands: add brands
[ ] Admin → Products: add products + upload images
[ ] Admin → Pages: complete legal pages (marked for legal review)
[ ] Admin → Markets: add verified import/export countries only
[ ] Replace assets/images/*.svg with real/generated photography
[ ] robots.txt + sitemap.php → update yourdomain.com
[ ] Test: register → order (COD) → admin ship → track order → invoice
[ ] Test: Razorpay test-mode payment end-to-end

============================================================
07. FOLDER PERMISSIONS
============================================================
assets/uploads/  → 755 (777 only if your host requires it)
Everything else  → 644 / 755

============================================================
08. BACKUP & MIGRATION
============================================================
- DB backup:  cPanel → phpMyAdmin → Export
- Files:      zip public_html (except cache/temp)
- Move host:  export DB → import on new host → update config.php SITE_URL
- Change domain: update SITE_URL in config.php + site_settings not needed
- Change payment keys: Admin → Settings
- Maintenance mode: Admin → Settings → Maintenance Mode = ON

============================================================
09. SECURITY NOTES (already implemented)
============================================================
PDO prepared statements · CSRF tokens on all forms · password_hash
XSS escaping (e()) · MIME-validated uploads · random upload filenames
Server-side cart/total recalculation · payment signature verification
Webhook signature check · idempotent payment handling · login throttle
via session regeneration · admin role separation

============================================================
10. STRUCTURE
============================================================
/                 storefront pages (PHP)
/includes         core (config, db, functions, auth, csrf, header, footer)
/ajax             JSON endpoints (cart, search, filters, wishlist...)
/payment          Razorpay create-order / verify / webhook callback
/admin            full admin panel (dark navy dashboard)
/assets           css, js, images, uploads
/database.sql     complete schema + seed data

© 2026 Mahaluxmi Trading House Pvt. Ltd. — Bathinda, Punjab, India
