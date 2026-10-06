<?php $y = date('Y'); ?>
</main>
<footer class="footer">
  <div class="container footer-grid">
    <div class="f-brand">
      <a href="<?= SITE_URL ?>/" class="logo"><span class="logo-mark"><img src="<?= SITE_URL ?>/assets/images/mth-heritage-crest.svg" alt="" aria-hidden="true"></span><span class="logo-text">MAHALUXMI<small>TRADING HOUSE PVT. LTD.</small></span></a>
      <p>A diversified trading house serving industrial supply and premium food product requirements — wholesale, import & export.</p>
      <p class="f-cin">CIN: <?= e(setting('cin')) ?></p>
    </div>
    <div class="f-col"><h5>Shop</h5><a href="category.php?slug=industrial-electrical">Industrial</a><a href="category.php?slug=agri-food-dry-fruits">Food & Agriculture</a><a href="shop.php">All Products</a><a href="shop.php?offers=1">Offers</a></div>
    <div class="f-col"><h5>Business</h5><a href="business.php">Wholesale / B2B</a><a href="import-export.php">Import / Export</a><a href="bulk-quote.php">Bulk Quote</a><a href="business.php#register">Become a Business Customer</a></div>
    <div class="f-col"><h5>Company</h5><a href="about.php">About Us</a><a href="blog.php">Blog</a><a href="videos.php">Videos</a><a href="contact.php">Contact</a></div>
    <div class="f-col"><h5>Support</h5><a href="track-order.php">Track Order</a><a href="account.php">My Account</a><a href="page.php?slug=shipping-policy">Shipping Policy</a><a href="page.php?slug=return-refund-policy">Returns & Refunds</a><a href="faq.php">FAQ</a></div>
    <div class="f-col f-contact">
      <h5>Contact</h5>
      <p><strong>Registered Address:</strong><br><?= nl2br(e(setting('registered_address'))) ?></p>
      <p><a href="tel:<?= preg_replace('/\s+/', '', setting('company_mobile')) ?>"><?= e(setting('company_mobile')) ?></a><br>
      <a href="tel:+911642250015">+91 164 2250015</a> · <a href="tel:+911645006992">+91 164 5006992</a><br>
      <a href="mailto:<?= e(setting('company_email')) ?>"><?= e(setting('company_email')) ?></a></p>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container fb-in">
      <span>© <?= $y ?> Mahaluxmi Trading House Pvt. Ltd. All rights reserved.</span>
      <div class="legal">
        <a href="page.php?slug=privacy-policy">Privacy</a>
        <a href="page.php?slug=terms-conditions">Terms</a>
        <a href="page.php?slug=shipping-policy">Shipping</a>
        <a href="page.php?slug=cancellation-policy">Cancellation</a>
        <a href="page.php?slug=return-refund-policy">Return/Refund</a>
        <a href="page.php?slug=cookie-policy">Cookies</a>
      </div>
    </div>
  </div>
</footer>

<!-- Cart drawer -->
<div class="drawer-overlay" id="drawerOverlay"></div>
<aside class="cart-drawer" id="cartDrawer">
  <div class="cd-head"><h4>Your Cart</h4><button id="cartClose" aria-label="Close">✕</button></div>
  <div class="cd-body" id="cartDrawerBody"><p class="muted">Loading cart...</p></div>
  <div class="cd-foot"><a href="checkout.php" class="btn btn-primary btn-block">Secure Checkout</a><a href="cart.php" class="btn btn-ghost btn-block">View Cart</a></div>
</aside>

<!-- Floating actions -->
<div class="float-actions">
  <?php if (setting('whatsapp')): ?>
  <a class="fa-btn fa-wa" href="https://wa.me/<?= preg_replace('/\D/', '', setting('whatsapp')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg class="ic"><use href="#i-wa"/></svg></a>
  <?php endif; ?>
  <a class="fa-btn" href="tel:<?= preg_replace('/\s+/', '', setting('company_mobile')) ?>" aria-label="Call"><svg class="ic"><use href="#i-phone"/></svg></a>
  <a class="fa-btn" href="mailto:<?= e(setting('company_email')) ?>" aria-label="Email"><svg class="ic"><use href="#i-mail"/></svg></a>
</div>

<!-- Mobile bottom nav -->
<nav class="mobile-nav">
  <a href="index.php"><svg class="ic"><use href="#i-home"/></svg>Home</a>
  <a href="shop.php"><svg class="ic"><use href="#i-grid"/></svg>Shop</a>
  <a href="#" id="mSearch"><svg class="ic"><use href="#i-search"/></svg>Search</a>
  <a href="account.php"><svg class="ic"><use href="#i-user"/></svg>Account</a>
  <a href="#" id="mCart"><svg class="ic"><use href="#i-cart"/></svg>Cart</a>
</nav>

<!-- SVG icons -->
<svg width="0" height="0" style="position:absolute">
<defs>
<symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3c-3 3.5-3 14.5 0 18" fill="none" stroke="currentColor" stroke-width="1.4"/></symbol>
<symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M21 21l-4.5-4.5" stroke="currentColor" stroke-width="1.8"/></symbol>
<symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M4 21c1.5-4 5-6 8-6s6.5 2 8 6" fill="none" stroke="currentColor" stroke-width="1.7"/></symbol>
<symbol id="i-heart" viewBox="0 0 24 24"><path d="M12 21C7 16.5 3 13 3 8.8 3 6 5.2 4 7.8 4c1.7 0 3.2.8 4.2 2.2C13 4.8 14.5 4 16.2 4 18.8 4 21 6 21 8.8c0 4.2-4 7.7-9 12.2z" fill="none" stroke="currentColor" stroke-width="1.7"/></symbol>
<symbol id="i-cart" viewBox="0 0 24 24"><path d="M3 4h2l2.5 12h11L21 8H7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="9.5" cy="20" r="1.5" fill="currentColor"/><circle cx="17" cy="20" r="1.5" fill="currentColor"/></symbol>
<symbol id="i-phone" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5c1 2.5 3 4.5 5.5 5.5L15 13.5 20 15.5v4c0 1-1 2-2 2C9 21 3 15 3 6c0-1 1-2 2-2z" fill="none" stroke="currentColor" stroke-width="1.6"/></symbol>
<symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3 7l9 6 9-6" fill="none" stroke="currentColor" stroke-width="1.6"/></symbol>
<symbol id="i-compare" viewBox="0 0 24 24"><path d="M9 3v18M15 3v18M4 8h5M4 16h5M15 8h5M15 16h5" stroke="currentColor" stroke-width="1.7" fill="none"/></symbol>
<symbol id="i-wa" viewBox="0 0 24 24"><path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M8.5 9.5c0 3.5 3 6 6 6l1-1.5-2-1-1 .5c-1-.5-2-1.5-2.5-2.5l.5-1-1-2z" fill="currentColor"/></symbol>
<symbol id="i-home" viewBox="0 0 24 24"><path d="M4 11l8-7 8 7v9a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1z" fill="none" stroke="currentColor" stroke-width="1.6"/></symbol>
<symbol id="i-grid" viewBox="0 0 24 24"><rect x="4" y="4" width="7" height="7" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="4" width="7" height="7" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.6"/><rect x="4" y="13" width="7" height="7" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="13" width="7" height="7" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.6"/></symbol>
<symbol id="i-check" viewBox="0 0 24 24"><path d="M4 12l5 5L20 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></symbol>
</defs>
</svg>
<div class="toast" id="toast"></div>
<script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
</body>
</html>
