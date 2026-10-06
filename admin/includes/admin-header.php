<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
$asec = basename($_SERVER['PHP_SELF'], '.php');
$fl = flash();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Admin — <?= e(setting('site_name')) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head><body><div class="admin-shell">
<aside class="admin-side">
  <a href="<?= SITE_URL ?>/" class="logo"><span class="logo-mark">◆</span><span class="logo-text">MAHALUXMI<small>ADMIN PANEL</small></span></a>
  <div class="grp">Main</div>
  <a href="dashboard.php" class="<?= $asec==='dashboard'?'active':'' ?>">📊 Dashboard</a>
  <div class="grp">Catalog</div>
  <a href="products.php" class="<?= $asec==='products'?'active':'' ?>">📦 Products</a>
  <a href="categories.php" class="<?= $asec==='categories'?'active':'' ?>">🗂 Categories</a>
  <a href="brands.php" class="<?= $asec==='brands'?'active':'' ?>">🏷 Brands</a>
  <a href="inventory.php" class="<?= $asec==='inventory'?'active':'' ?>">📊 Inventory</a>
  <a href="reviews.php" class="<?= $asec==='reviews'?'active':'' ?>">⭐ Reviews</a>
  <div class="grp">Orders</div>
  <a href="orders.php" class="<?= $asec==='orders'?'active':'' ?>">🧾 Orders</a>
  <a href="quotes.php" class="<?= $asec==='quotes'?'active':'' ?>">💬 Bulk Quotes</a>
  <div class="grp">Customers</div>
  <a href="customers.php" class="<?= $asec==='customers'?'active':'' ?>">👥 Customers</a>
  <a href="b2b.php" class="<?= $asec==='b2b'?'active':'' ?>">🏢 B2B Approvals</a>
  <div class="grp">Sales</div>
  <a href="coupons.php" class="<?= $asec==='coupons'?'active':'' ?>">🎟 Coupons</a>
  <div class="grp">Content</div>
  <a href="blogs.php" class="<?= $asec==='blogs'?'active':'' ?>">📝 Blogs</a>
  <a href="pages.php" class="<?= $asec==='pages'?'active':'' ?>">📄 Pages</a>
  <a href="videos.php" class="<?= $asec==='videos-admin'?'active':'' ?>">🎬 Videos</a>
  <a href="faqs.php" class="<?= $asec==='faqs'?'active':'' ?>">❓ FAQs</a>
  <a href="testimonials.php" class="<?= $asec==='testimonials'?'active':'' ?>">💬 Testimonials</a>
  <div class="grp">System</div>
  <a href="markets.php" class="<?= $asec==='markets'?'active':'' ?>">🌍 Trade Markets</a>
  <a href="settings.php" class="<?= $asec==='settings'?'active':'' ?>">⚙️ Settings</a>
  <a href="logout.php">🚪 Logout</a>
</aside>
<main class="admin-main">
