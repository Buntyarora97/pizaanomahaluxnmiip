<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
$pageTitle = $pageTitle ?? setting('site_name');
$pageDesc = $pageDesc ?? 'Diversified trading house — Industrial & Electrical + Agri, Food & Dry Fruits. Wholesale, Import & Export.';
$canonical = $canonical ?? SITE_URL . $_SERVER['REQUEST_URI'];
$cc = cartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDesc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDesc) ?>">
<meta property="og:type" content="website">
<link rel="icon" href="<?= SITE_URL ?>/assets/images/favicon.svg" type="image/svg+xml">
<meta name="theme-color" content="#4B1118">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<script>const SITE_URL=<?= json_encode(SITE_URL, JSON_UNESCAPED_SLASHES) ?>;const CART_COUNT=<?= (int)$cc ?>;const CSRF_TOKEN=<?= json_encode(csrf_token()) ?>;</script>
</head>
<body>
<div class="topbar">
  <div class="container topbar-in">
    <div class="tb-left">
      <a href="tel:<?= preg_replace('/\s+/', '', setting('company_mobile')) ?>"><svg class="ic"><use href="#i-phone"/></svg><?= e(setting('company_mobile')) ?></a>
      <a href="mailto:<?= e(setting('company_email')) ?>"><svg class="ic"><use href="#i-mail"/></svg><?= e(setting('company_email')) ?></a>
    </div>
    <div class="tb-right">
      <a href="track-order.php">Track Order</a>
      <a href="bulk-quote.php">Bulk Enquiry</a>
      <a href="business.php">B2B</a>
      <a href="contact.php">Help</a>
    </div>
  </div>
</div>
<header class="header" id="header">
  <div class="container header-in">
    <a href="<?= SITE_URL ?>/" class="logo">
      <span class="logo-mark"><img src="<?= SITE_URL ?>/assets/images/mth-heritage-crest.svg" alt="" aria-hidden="true"></span>
      <span class="logo-text">MAHALUXMI<small>TRADING HOUSE PVT. LTD.</small></span>
    </a>
    <div class="search-wrap">
      <svg class="ic"><use href="#i-search"/></svg>
      <input type="search" id="ajaxSearch" placeholder="Search products, brands, SKU..." autocomplete="off">
      <div class="search-results" id="searchResults"></div>
    </div>
    <nav class="header-actions">
      <a href="account.php" class="hact"><svg class="ic"><use href="#i-user"/></svg><span>Account</span></a>
      <a href="wishlist.php" class="hact"><svg class="ic"><use href="#i-heart"/></svg><span>Wishlist</span></a>
      <a href="compare.php" class="hact"><svg class="ic"><use href="#i-compare"/></svg><span>Compare</span></a>
      <button class="hact cart-btn" id="cartOpen"><svg class="ic"><use href="#i-cart"/></svg><span>Cart</span><em class="cart-count" id="cartCount"><?= $cc ?></em></button>
    </nav>
    <button class="burger" id="burger" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
  <nav class="mainnav" id="mainnav">
    <div class="container mainnav-in">
      <a href="index.php">Home</a>
      <div class="nav-item has-mega">
        <a href="shop.php">Shop</a>
        <div class="mega">
          <div class="container mega-in">
            <div class="mega-col">
              <h5>Industrial & Electrical</h5>
              <?php
              $mc = $pdo->query("SELECT name,slug FROM categories WHERE parent_id=1 AND status=1 ORDER BY sort_order")->fetchAll();
              foreach ($mc as $c): ?><a href="category.php?slug=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
            </div>
            <div class="mega-col">
              <h5>Agri, Food & Dry Fruits</h5>
              <?php
              $mc = $pdo->query("SELECT name,slug FROM categories WHERE parent_id=2 AND status=1 ORDER BY sort_order")->fetchAll();
              foreach ($mc as $c): ?><a href="category.php?slug=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
            </div>
            <div class="mega-col">
              <h5>Featured</h5>
              <a href="shop.php?sort=new">New Arrivals</a>
              <a href="shop.php?sort=best">Best Sellers</a>
              <a href="shop.php?offers=1">Offers</a>
            </div>
            <div class="mega-promo">
              <span>Wholesale & B2B Supply</span>
              <a href="bulk-quote.php" class="btn btn-gold btn-sm">Request Bulk Quote</a>
            </div>
          </div>
        </div>
      </div>
      <a href="category.php?slug=industrial-electrical">Industrial & Electrical</a>
      <a href="category.php?slug=agri-food-dry-fruits">Food & Agriculture</a>
      <a href="shop.php?brands=1">Brands</a>
      <a href="business.php">B2B</a>
      <a href="import-export.php">Import & Export</a>
      <a href="about.php">About</a>
      <a href="blog.php">Resources</a>
      <a href="contact.php">Contact</a>
    </div>
  </nav>
</header>
<main>
