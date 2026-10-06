<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'B2B & Wholesale — ' . setting('site_name');
$canonical = SITE_URL . '/business';
include __DIR__ . '/includes/header.php';
?>
<section class="hero" style="padding:5rem 0"><div class="hero-grid-bg"></div>
  <div class="container" style="position:relative;z-index:2">
    <span class="hero-badge">Wholesale • Dealer • Institutional • Global</span>
    <h1>BUYING FOR <span class="grad">BUSINESS?</span></h1>
    <p class="lead">Partner with Mahaluxmi Trading House for bulk supply, wholesale pricing, institutional procurement and import/export requirements.</p>
    <div class="hero-ctas"><a href="bulk-quote.php" class="btn btn-gold">REQUEST BULK QUOTE</a><a href="#register" class="btn btn-ghost" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.3)">BECOME A BUSINESS CUSTOMER</a></div>
  </div>
</section>
<section class="section"><div class="container">
  <div class="sec-head reveal"><span class="sec-eyebrow">B2B Benefits</span><h2>Built for Business Buyers</h2></div>
  <div class="why-grid">
    <?php
    $b = [
      ['▦','Bulk Purchase','Order in quantity with structured quotations for your procurement needs.'],
      ['₹','Wholesale Pricing','Approved business customers can access tiered wholesale pricing.'],
      ['▤','Institutional Procurement','Supply programs for institutions, dealers and large organisations.'],
      ['⇄','Import Requirements','Tell us what you need imported — we handle the trade conversation.'],
      ['◉','Export Enquiries','Explore export opportunities through our trade network.'],
      ['✆','Dedicated Support','Direct line to our trade team for quotes, orders and documentation.']
    ];
    foreach ($b as $x): ?><div class="why-card reveal"><div class="why-ic"><?= $x[0] ?></div><h4><?= $x[1] ?></h4><p><?= $x[2] ?></p></div><?php endforeach; ?>
  </div>
</div></section>
<section class="section" style="background:#fff" id="register"><div class="container" style="max-width:640px">
  <div class="sec-head reveal"><span class="sec-eyebrow">Registration</span><h2>Become a Business Customer</h2><p>Create a free account and apply for B2B approval from your dashboard.</p></div>
  <div class="acct-card reveal">
    <div style="display:grid;gap:1rem">
      <a href="register.php" class="btn btn-primary btn-block">CREATE ACCOUNT &amp; APPLY</a>
      <p class="muted" style="text-align:center;font-size:.85rem">Already have an account? <a href="login.php" style="color:var(--navy2)">Login</a> and apply from Business Profile.</p>
    </div>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
