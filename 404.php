<?php
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = 'Page Not Found — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="nf">
  <div class="hero-grid-bg"></div>
  <div style="position:relative;z-index:2" class="reveal vis">
    <div class="fc-sphere" style="width:120px;height:120px"></div>
    <h1>404</h1>
    <h2 style="font-size:1.4rem">Looks Like This Product Took Another Route.</h2>
    <p>The page you're looking for doesn't exist or has moved.</p>
    <div class="hero-ctas" style="justify-content:center"><a href="shop.php" class="btn btn-primary">SHOP PRODUCTS</a><a href="index.php" class="btn btn-ghost" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.3)">GO HOME</a></div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
