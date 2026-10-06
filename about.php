<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us — ' . setting('site_name');
$pageDesc = 'Mahaluxmi Trading House Pvt. Ltd. — incorporated 5 April 2019, Bathinda, Punjab. Manufacturer, Exporter, Importer, Wholesale Supplier, Buyer & Trader.';
$canonical = SITE_URL . '/about';
include __DIR__ . '/includes/header.php';
?>
<section class="hero" style="padding:5rem 0"><div class="hero-grid-bg"></div>
  <div class="container" style="position:relative;z-index:2;text-align:center">
    <span class="hero-badge">Since 5 April 2019</span>
    <h1 style="max-width:800px;margin:1.4rem auto">MAHALUXMI TRADING HOUSE<br><span class="grad">PVT. LTD.</span></h1>
    <p class="lead" style="margin:0 auto">"Trade Built Around Opportunity."</p>
    <div class="hero-ctas" style="justify-content:center"><a href="business.php" class="btn btn-gold">START A BUSINESS CONVERSATION</a></div>
  </div>
</section>
<section class="section"><div class="container" style="max-width:820px">
  <div class="sec-head reveal"><span class="sec-eyebrow">Who We Are</span><h2>A Diversified Trading House</h2></div>
  <p class="muted reveal" style="line-height:1.9;text-align:center">Mahaluxmi Trading House Pvt. Ltd. is a Bathinda-based private limited company incorporated on <b>5 April 2019</b> (CIN: <?= e(setting('cin')) ?>). We operate as a <b>manufacturer, exporter, importer, wholesale supplier, buyer and trader</b> — serving industrial supply and premium food product requirements across multiple categories.</p>
</div></section>
<section class="section" style="background:#fff;padding-top:3rem"><div class="container">
  <div class="sec-head reveal"><span class="sec-eyebrow">Our Journey</span><h2>Timeline</h2></div>
  <div style="max-width:560px;margin:0 auto">
    <div class="acct-card reveal" style="border-left:4px solid var(--cyan)"><b style="color:var(--navy)">5 April 2019</b><p class="muted" style="margin-top:.4rem">Mahaluxmi Trading House Pvt. Ltd. incorporated in Bathinda, Punjab.</p></div>
    <div class="acct-card reveal" style="border-left:4px solid var(--gold);opacity:.7"><b style="color:var(--navy)">Growing every day</b><p class="muted" style="margin-top:.4rem">New milestones are added here as our journey continues.</p></div>
  </div>
</div></section>
<section class="section"><div class="container">
  <div class="sec-head reveal"><span class="sec-eyebrow">What We Do</span><h2>Our Business Capabilities</h2></div>
  <div class="caps-in" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
    <?php foreach (['Manufacturer','Exporter','Importer','Wholesale Supplier','Buyer','Trader'] as $c): ?>
    <div class="cap reveal" style="background:var(--navy)"><span>◆</span><b><?= $c ?></b></div>
    <?php endforeach; ?>
  </div>
</div></section>
<section class="section" style="background:#fff"><div class="container">
  <div class="sec-head reveal"><span class="sec-eyebrow">Verticals</span><h2>Our Business Verticals</h2></div>
  <div class="worlds">
    <a href="category.php?slug=industrial-electrical" class="world w-ind reveal"><div class="w-in"><span class="tag">Vertical 01</span><h3>Industrial &amp; Electrical</h3><p>Electrical &amp; electronic components, circuit breakers, fuses, electric motors, sensors and related industrial goods.</p></div></a>
    <a href="category.php?slug=agri-food-dry-fruits" class="world w-food reveal"><div class="w-in"><span class="tag">Vertical 02</span><h3>Agriculture &amp; Food</h3><p>Agricultural &amp; processed food, dry fruits, nuts, peanuts, food products and beverages.</p></div></a>
  </div>
</div></section>
<section class="section"><div class="container">
  <div class="sec-head reveal"><span class="sec-eyebrow">Leadership</span><h2>Our Directors</h2></div>
  <div class="why-grid" style="grid-template-columns:1fr 1fr;max-width:720px;margin:0 auto">
    <div class="why-card reveal" style="text-align:center"><div class="rev-av" style="margin:0 auto 1rem;width:72px;height:72px;font-size:1.6rem">SG</div><h4>Sudershan Gupta</h4><p>Director</p></div>
    <div class="why-card reveal" style="text-align:center"><div class="rev-av" style="margin:0 auto 1rem;width:72px;height:72px;font-size:1.6rem">SG</div><h4>Sidhant Gupta</h4><p>Director</p></div>
  </div>
</div></section>
<section class="section" style="background:#fff"><div class="container">
  <div class="sec-head reveal"><span class="sec-eyebrow">How We Work</span><h2>Our Process</h2></div>
  <div class="caps-in" style="display:grid;grid-template-columns:repeat(5,1fr);gap:1rem">
    <?php foreach (['Source','Evaluate','Trade','Supply','Support'] as $i => $s): ?>
    <div class="cap reveal" style="background:linear-gradient(135deg,var(--navy),var(--navy2))"><span>0<?= $i+1 ?></span><b><?= $s ?></b></div>
    <?php endforeach; ?>
  </div>
  <div class="sec-head reveal" style="margin-top:4rem"><span class="sec-eyebrow">Our Approach</span><h2>What Guides Us</h2></div>
  <div class="why-grid" style="grid-template-columns:repeat(4,1fr)">
    <?php foreach (['Quality','Transparency','Responsiveness','Long-Term Relationships'] as $a): ?>
    <div class="why-card reveal" style="text-align:center"><h4><?= $a ?></h4></div>
    <?php endforeach; ?>
  </div>
</div></section>
<section class="section b2b-band"><div class="container" style="text-align:center;position:relative;z-index:2">
  <h2 class="reveal">Start a Business Conversation</h2>
  <p class="reveal" style="margin:0 auto 2rem">Wholesale, procurement, import or export — let's talk.</p>
  <div class="hero-ctas reveal" style="justify-content:center"><a href="contact.php" class="btn btn-gold">CONTACT US</a><a href="bulk-quote.php" class="btn btn-ghost" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.3)">BULK QUOTE</a></div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
