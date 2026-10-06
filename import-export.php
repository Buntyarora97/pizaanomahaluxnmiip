<?php
require_once __DIR__ . '/includes/functions.php';
$markets = $pdo->query('SELECT * FROM trade_markets WHERE status=1')->fetchAll();
$pageTitle = 'Import & Export — ' . setting('site_name');
$canonical = SITE_URL . '/import-export';
include __DIR__ . '/includes/header.php';
?>
<section class="hero" style="padding:5rem 0"><div class="hero-grid-bg"></div>
  <div class="container" style="position:relative;z-index:2">
    <span class="hero-badge">Global Trade • Verified Markets</span>
    <h1>IMPORT &amp; <span class="grad">EXPORT</span></h1>
    <p class="lead">Global trade support for industrial goods and food products — with verified trade routes and professional handling.</p>
    <div class="hero-ctas"><a href="bulk-quote.php" class="btn btn-gold">REQUEST IMPORT/EXPORT QUOTE</a></div>
  </div>
</section>
<section class="section"><div class="container">
  <div class="sec-head reveal"><span class="sec-eyebrow">Capabilities</span><h2>What We Handle</h2></div>
  <div class="why-grid">
    <div class="why-card reveal"><div class="why-ic">⇣</div><h4>Import Enquiries</h4><p>Sourcing and import support for products your business requires.</p></div>
    <div class="why-card reveal"><div class="why-ic">⇡</div><h4>Export Enquiries</h4><p>Export opportunities for Indian industrial and food products.</p></div>
    <div class="why-card reveal"><div class="why-ic">▦</div><h4>Product Categories</h4><p>Electrical &amp; electronic components and agri/processed food products.</p></div>
  </div>
</div></section>
<section class="section trade"><div class="container">
  <div class="sec-head on-dark reveal"><span class="sec-eyebrow">Verified Markets</span><h2>Where We Trade</h2><p>Only verified, admin-managed markets are shown below.</p></div>
  <?php if ($markets): ?><div class="promo-grid">
    <?php foreach ($markets as $m): ?><div class="promo reveal" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1)"><span class="tag"><?= e(strtoupper($m['trade_type'])) ?></span><h4><?= e($m['country']) ?></h4><p><?= e($m['products']) ?></p></div><?php endforeach; ?>
  </div>
  <?php else: ?><p style="text-align:center;color:#8fa6c0" class="reveal">Our trade network details are published here after verification. <a href="bulk-quote.php" style="color:var(--cyan);font-weight:600">Send an enquiry →</a></p><?php endif; ?>
</div></section>
<section class="section"><div class="container" style="max-width:720px">
  <div class="sec-head reveal"><span class="sec-eyebrow">Process</span><h2>How It Works</h2></div>
  <div class="timeline" style="margin-top:0">
    <?php foreach (['Enquiry','Evaluation','Quotation','Trade Execution','Delivery & Support'] as $s): ?><div class="tl-step done"><span><?= $s ?></span></div><?php endforeach; ?>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
