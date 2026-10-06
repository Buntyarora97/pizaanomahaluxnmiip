<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = setting('site_name') . ' — ' . setting('site_tagline');
$pageDesc = 'Mahaluxmi Trading House Pvt. Ltd. — Industrial & Electrical + Agri, Food & Dry Fruits. Wholesale supply, import & export. Bathinda, Punjab, India.';
$canonical = SITE_URL . '/';

// Dynamic data
$featured = $pdo->query("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, b.name AS brand_name,
  (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS img,
  (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1 OFFSET 1) AS img2
  FROM products p JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.brand_id
  WHERE p.status=1 AND p.is_featured=1 ORDER BY p.updated_at DESC LIMIT 8")->fetchAll();
$best = $pdo->query("SELECT p.*, c.name AS cat_name,
  (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS img
  FROM products p JOIN categories c ON c.id=p.category_id WHERE p.status=1 AND p.is_bestseller=1 LIMIT 8")->fetchAll();
$cats = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id=c.id AND status=1) AS pcount,
  (SELECT image FROM product_images pi JOIN products p2 ON p2.id=pi.product_id WHERE p2.category_id=c.id ORDER BY pi.id LIMIT 1) AS img
  FROM categories c WHERE c.parent_id IS NOT NULL AND c.status=1 ORDER BY c.sort_order LIMIT 8")->fetchAll();
$heroProducts = $pdo->query("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug,
  (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS img
  FROM products p JOIN categories c ON c.id=p.category_id WHERE p.status=1 AND p.is_featured=1 ORDER BY p.updated_at DESC LIMIT 4")->fetchAll();
$reviews = $pdo->query("SELECT t.* FROM testimonials t WHERE t.status=1 ORDER BY t.id DESC LIMIT 6")->fetchAll();
$blogs = $pdo->query("SELECT b.*, bc.name AS cat_name FROM blogs b LEFT JOIN blog_categories bc ON bc.id=b.category_id WHERE b.status='published' ORDER BY b.published_at DESC LIMIT 3")->fetchAll();
$markets = $pdo->query("SELECT * FROM trade_markets WHERE status=1 LIMIT 8")->fetchAll();
$videos = $pdo->query("SELECT * FROM videos WHERE status=1 AND attached_to='home' ORDER BY sort_order LIMIT 1")->fetchAll();
$heroVid = $videos[0] ?? null;

function discount($p) { return $p['mrp'] && $p['mrp'] > $p['price'] ? round((1 - $p['price'] / $p['mrp']) * 100) : 0; }
include __DIR__ . '/includes/header.php';
?>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"Organization","name":"Mahaluxmi Trading House Pvt. Ltd.","url":"<?= SITE_URL ?>","email":"<?= e(setting('company_email')) ?>","telephone":"<?= e(setting('company_mobile')) ?>","address":{"@type":"PostalAddress","streetAddress":"1st Floor, Plot No. 2029, The Mall Road","addressLocality":"Bathinda","addressRegion":"Punjab","postalCode":"151001","addressCountry":"IN"}}
</script>

<!-- ============ SECTION 01: CINEMATIC SCROLL HERO ============ -->
<section class="cinematic-hero" id="cinematicHero">
  <div class="cinematic-stage">
    <video class="cinematic-video" id="scrollVideo" muted playsinline preload="auto"
      poster="<?= SITE_URL ?>/assets/uploads/mth/products/pluckers-orange-marmalade.jpg"
      aria-label="Mahaluxmi Trading House product and trade film">
      <source src="<?= SITE_URL ?>/video-stream.php" type="video/mp4">
    </video>
    <div class="cinematic-wash"></div>
    <div class="cinematic-grain"></div>
    <div class="cinematic-orbit orbit-one"></div>
    <div class="cinematic-orbit orbit-two"></div>
    <div class="container cinematic-content">
      <div class="cinematic-copy">
        <span class="cinematic-kicker"><i></i> Mahaluxmi Trading House</span>
        <h1>From source.<br><em>To shelf.</em><br>With purpose.</h1>
        <p>Premium food products and dependable trade supply, brought together from Bathinda to the markets that matter.</p>
        <div class="hero-ctas">
          <a href="shop.php" class="btn btn-gold">Explore the collection <span>↗</span></a>
          <a href="bulk-quote.php" class="cinematic-link">Talk to our trade desk <span>→</span></a>
        </div>
      </div>
      <div class="cinematic-side">
        <div class="film-index"><span>01</span><b>FOOD · TRADE · SUPPLY</b></div>
        <div class="film-scroll"><span>Scroll to explore</span><i></i></div>
      </div>
    </div>
    <div class="cinematic-product-note"><span>Featured</span><b>Pluckers · Aasmi · Jeenia</b></div>
  </div>
</section>

<!-- ============ SECTION 02: CAPABILITIES ============ -->
<section class="caps">
  <div class="container caps-in">
    <?php foreach (['Manufacturer','Exporter','Importer','Wholesale Supplier','Buyer','Trader'] as $c): ?>
    <div class="cap"><span>◆</span><b><?= $c ?></b></div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ SECTION 03: SHOP BY WORLD ============ -->
<section class="section">
  <div class="container">
    <div class="sec-head reveal"><span class="sec-eyebrow">Two Business Worlds</span><h2>Shop by World</h2><p>One trading house. Two specialised supply worlds — precision industrial components and premium food products.</p></div>
    <div class="worlds">
      <a href="category.php?slug=industrial-electrical" class="world w-ind tilt reveal">
        <img class="bg" src="<?= SITE_URL ?>/assets/images/world-industrial.jpg" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
        <div class="w-in"><span class="tag">World 01</span><h3>Industrial &amp; Electrical</h3><p>Circuit breakers, fuses, electric motors, sensors and precision electrical components for industrial and commercial supply.</p><span class="btn btn-primary btn-sm">Explore Industrial →</span></div>
      </a>
      <a href="category.php?slug=agri-food-dry-fruits" class="world w-food tilt reveal">
        <img class="bg" src="<?= SITE_URL ?>/assets/images/world-food.jpg" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
        <div class="w-in"><span class="tag">World 02</span><h3>Agri, Food &amp; Dry Fruits</h3><p>Almonds, cashews, nuts, peanuts, processed foods and beverages — quality food products for retail and wholesale.</p><span class="btn btn-gold btn-sm">Explore Food &amp; Agriculture →</span></div>
      </a>
    </div>
  </div>
</section>

<!-- ============ SECTION 04: CATEGORIES ============ -->
<?php if ($cats): ?>
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="sec-head reveal"><span class="sec-eyebrow">Browse</span><h2>Shop by Category</h2></div>
    <div class="cat-grid">
      <?php foreach ($cats as $c): ?>
      <a href="category.php?slug=<?= e($c['slug']) ?>" class="cat-card reveal">
        <div class="cc-img"><?php if ($c['img']): ?><img src="<?= e(productImage($c['img'])) ?>" alt="<?= e($c['name']) ?>" loading="lazy"><?php endif; ?></div>
        <h4><?= e($c['name']) ?></h4><span><?= $c['pcount'] ?> product<?= $c['pcount'] != 1 ? 's' : '' ?></span>
        <span class="go">Explore →</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ SECTION 05: FEATURED PRODUCTS ============ -->
<?php if ($featured): ?>
<section class="section" style="background:#fff">
  <div class="container">
    <div class="sec-head reveal"><span class="sec-eyebrow">Curated Selection</span><h2>Featured Products</h2><p>Hand-picked products from both our business verticals, updated by our team.</p></div>
    <div class="p-grid">
      <?php foreach (array_slice($featured, 0, 4) as $p): $off = discount($p); ?>
      <div class="p-card reveal">
        <div class="p-img">
          <div class="p-badges">
            <?php if ($p['is_new']): ?><span class="p-badge new">NEW</span><?php endif; ?>
            <?php if ($off): ?><span class="p-badge off">-<?= $off ?>%</span><?php endif; ?>
          </div>
          <a href="product.php?slug=<?= e($p['slug']) ?>"><img src="<?= e(productImage($p['img'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a>
          <div class="p-actions">
            <button class="pa-btn js-wish" data-id="<?= $p['id'] ?>" title="Wishlist"><svg class="ic"><use href="#i-heart"/></svg></button>
            <button class="pa-btn js-compare" data-id="<?= $p['id'] ?>" title="Compare"><svg class="ic"><use href="#i-compare"/></svg></button>
          </div>
        </div>
        <div class="p-body">
          <span class="p-brand"><?= e($p['brand_name'] ?: $p['cat_name']) ?></span>
          <span class="p-name"><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></span>
          <div class="p-price">
            <?php if ($p['quote_only'] || !$p['purchasable']): ?><span class="now" style="font-size:.92rem">Request Quote</span>
            <?php else: ?><span class="now"><?= money($p['price']) ?></span>
              <?php if ($p['mrp'] && $p['mrp'] > $p['price']): ?><span class="mrp"><?= money($p['mrp']) ?></span><span class="off"><?= $off ?>% off</span><?php endif; ?>
            <?php endif; ?>
          </div>
          <span class="p-stock <?= $p['stock'] > 0 ? 'in' : 'out' ?>"><?= $p['stock'] > 0 ? '● In Stock' : '● Out of Stock' ?></span>
          <?php if ($p['stock'] > 0 && $p['purchasable'] && !$p['quote_only']): ?>
            <button class="btn btn-primary btn-sm js-add-cart" data-id="<?= $p['id'] ?>">Add to Cart</button>
          <?php else: ?>
            <a href="bulk-quote.php?product=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Request Quote</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2.5rem" class="reveal"><a href="shop.php" class="btn btn-dark">View All Products</a></div>
  </div>
</section>
<?php endif; ?>

<!-- ============ SECTION 06: B2B ============ -->
<section class="section b2b-band">
  <div class="container b2b-in">
    <div class="reveal">
      <span class="sec-eyebrow">Wholesale &amp; B2B</span>
      <h2>Buying for Business?</h2>
      <p>We support bulk quantity requirements, wholesale supply, institutional procurement, dealer enquiries and import/export requirements — with dedicated business assistance.</p>
      <div class="b2b-points">
        <div><svg class="ic"><use href="#i-check"/></svg>Bulk Quantity Supply</div>
        <div><svg class="ic"><use href="#i-check"/></svg>Wholesale Pricing Tiers</div>
        <div><svg class="ic"><use href="#i-check"/></svg>Institutional Procurement</div>
        <div><svg class="ic"><use href="#i-check"/></svg>Import / Export Support</div>
      </div>
      <div class="hero-ctas">
        <a href="bulk-quote.php" class="btn btn-gold">REQUEST BULK QUOTE</a>
        <a href="business.php" class="btn btn-ghost" style="background:transparent;color:#fff;border-color:rgba(255,255,255,.3)">BECOME A BUSINESS CUSTOMER</a>
      </div>
    </div>
    <div class="reveal" style="text-align:center">
      <div class="fc-sphere"></div>
      <p style="color:#a9bdd4;font-size:.9rem">Wholesale • Dealer • Institutional • Global Trade</p>
    </div>
  </div>
</section>

<!-- ============ SECTION 07: GLOBAL TRADE ============ -->
<section class="section trade">
  <div class="container">
    <div class="sec-head on-dark reveal"><span class="sec-eyebrow">Global Trade</span><h2>Connecting Markets</h2><p>Our trade network — markets shown below are verified trade routes managed by our team.</p></div>
    <div class="trade-map reveal">
      <svg class="trade-svg" viewBox="0 0 800 360" aria-hidden="true">
        <defs>
          <radialGradient id="glow"><stop offset="0%" stop-color="#00B8D9" stop-opacity=".8"/><stop offset="100%" stop-color="#00B8D9" stop-opacity="0"/></radialGradient>
        </defs>
        <ellipse cx="400" cy="185" rx="370" ry="165" fill="none" stroke="rgba(255,255,255,.08)"/>
        <ellipse cx="400" cy="185" rx="250" ry="110" fill="none" stroke="rgba(255,255,255,.06)"/>
        <ellipse cx="400" cy="185" rx="130" ry="55" fill="none" stroke="rgba(255,255,255,.05)"/>
        <circle cx="430" cy="200" r="60" fill="url(#glow)" opacity=".35"/>
        <circle cx="430" cy="200" r="7" fill="#C9A227"/>
        <text x="446" y="204" fill="#E5C86B" font-size="13" font-weight="700" font-family="Sora">INDIA</text>
        <?php
        $anchors = [[180,120],[620,100],[700,220],[140,250],[560,300],[300,80],[480,320],[680,150]];
        foreach ($markets as $i => $m): $a = $anchors[$i % 8]; ?>
        <path class="route-line" d="M430,200 Q<?= ($a[0]+430)/2 ?>,<?= min($a[1],200)-60 ?> <?= $a[0] ?>,<?= $a[1] ?>" fill="none" stroke="<?= $m['trade_type']==='import' ? '#C9A227' : '#00B8D9' ?>" stroke-width="1.6" opacity=".8"/>
        <circle cx="<?= $a[0] ?>" cy="<?= $a[1] ?>" r="4.5" fill="<?= $m['trade_type']==='import' ? '#C9A227' : '#00B8D9' ?>"/>
        <text x="<?= $a[0]+10 ?>" y="<?= $a[1]+4 ?>" fill="#b8cadd" font-size="12" font-family="Inter"><?= e(strtoupper($m['country'])) ?></text>
        <?php endforeach; if (!$markets): ?>
        <path class="route-line" d="M430,200 Q300,80 180,120" fill="none" stroke="#00B8D9" stroke-width="1.6" opacity=".6"/>
        <path class="route-line" d="M430,200 Q560,80 620,100" fill="none" stroke="#C9A227" stroke-width="1.6" opacity=".6"/>
        <text x="300" y="330" fill="#5B6B80" font-size="13" font-family="Inter">Trade routes are published after verification by our team.</text>
        <?php endif; ?>
      </svg>
      <div class="trade-legend"><span><b>●</b> Export markets</span><span><b style="color:#C9A227">●</b> Import markets</span><span>Verified &amp; admin-managed</span></div>
    </div>
  </div>
</section>

<!-- ============ SECTION 08: BEST SELLERS ============ -->
<?php if ($best): ?>
<section class="section">
  <div class="container">
    <div class="sec-head reveal"><span class="sec-eyebrow">Customer Favourites</span><h2>Best Sellers</h2></div>
    <div class="p-grid">
      <?php foreach (array_slice($best, 0, 4) as $p): $off = discount($p); ?>
      <div class="p-card reveal">
        <div class="p-img">
          <span class="p-badges"><span class="p-badge best">BEST SELLER</span></span>
          <a href="product.php?slug=<?= e($p['slug']) ?>"><img src="<?= e(productImage($p['img'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a>
          <div class="p-actions">
            <button class="pa-btn js-wish" data-id="<?= $p['id'] ?>"><svg class="ic"><use href="#i-heart"/></svg></button>
            <button class="pa-btn js-compare" data-id="<?= $p['id'] ?>"><svg class="ic"><use href="#i-compare"/></svg></button>
          </div>
        </div>
        <div class="p-body">
          <span class="p-brand"><?= e($p['cat_name']) ?></span>
          <span class="p-name"><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></span>
          <div class="p-price">
            <?php if ($p['quote_only']): ?><span class="now" style="font-size:.92rem">Request Quote</span>
            <?php else: ?><span class="now"><?= money($p['price']) ?></span>
              <?php if ($p['mrp'] && $p['mrp'] > $p['price']): ?><span class="mrp"><?= money($p['mrp']) ?></span><?php endif; ?>
            <?php endif; ?>
          </div>
          <?php if ($p['stock'] > 0 && $p['purchasable'] && !$p['quote_only']): ?>
            <button class="btn btn-primary btn-sm js-add-cart" data-id="<?= $p['id'] ?>">Add to Cart</button>
          <?php else: ?><a href="bulk-quote.php?product=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Request Quote</a><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ SECTION 09: WHY ============ -->
<section class="section" style="background:#fff">
  <div class="container">
    <div class="sec-head reveal"><span class="sec-eyebrow">Why Us</span><h2>Why Mahaluxmi</h2></div>
    <div class="why-grid">
      <?php
      $whys = [
        ['◈','Diversified Product Portfolio','Industrial & electrical plus premium food products — multiple categories from one trusted trading partner.'],
        ['⇄','B2B & Retail Ordering','Order online as a retail customer or set up a business account for wholesale requirements.'],
        ['▦','Wholesale Requirements','Dedicated support for bulk quantities, dealer supply and institutional procurement.'],
        ['◉','Import & Export Support','Enquiry handling for import requirements and export opportunities, managed professionally.'],
        ['▤','Business Procurement','Structured quotations, documentation support and responsive trade assistance.'],
        ['✆','Responsive Assistance','Phone, email and WhatsApp support for orders, quotes and trade enquiries.']
      ];
      foreach ($whys as $w): ?>
      <div class="why-card reveal"><div class="why-ic"><?= $w[0] ?></div><h4><?= $w[1] ?></h4><p><?= $w[2] ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ SECTION 10: VIDEO ============ -->
<?php if ($heroVid): ?>
<section class="section">
  <div class="container">
    <div class="video-band reveal">
      <?php if ($heroVid['thumbnail']): ?><img src="<?= e($heroVid['thumbnail']) ?>" alt="" loading="lazy"><?php endif; ?>
      <div class="vb-in">
        <button class="play-btn" onclick="window.open('<?= e($heroVid['video_url']) ?>','_blank')" aria-label="Play video">▶</button>
        <h2 style="font-size:1.6rem"><?= e($heroVid['title']) ?></h2>
        <p style="color:#b8cadd;margin-top:.5rem">Watch our products, processes and business in action.</p>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ SECTION 11: FOOD EXPERIENCE ============ -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="experience reveal">
      <img class="bg" src="<?= SITE_URL ?>/assets/images/exp-food.svg" alt="Premium dry fruits and nuts" loading="lazy" onerror="this.style.display='none'">
      <div class="exp-in">
        <span class="tag">World 02 — Food</span>
        <h2>Premium Dry Fruits &amp; Nuts</h2>
        <p>From almonds and cashews to walnuts, pistachios and peanuts — quality food products sourced and supplied for retail and wholesale customers.</p>
        <div class="exp-items"><span>Almonds</span><span>Cashews</span><span>Walnuts</span><span>Pistachios</span><span>Peanuts</span><span>Dry Fruits</span></div>
        <a href="category.php?slug=agri-food-dry-fruits" class="btn btn-gold">Shop Food &amp; Dry Fruits</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ SECTION 12: INDUSTRIAL EXPERIENCE ============ -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="experience reveal">
      <img class="bg" src="<?= SITE_URL ?>/assets/images/exp-industrial.svg" alt="Industrial electrical products" loading="lazy" onerror="this.style.display='none'">
      <div class="exp-in">
        <span class="tag">World 01 — Industrial</span>
        <h2>Industrial &amp; Electrical Supply</h2>
        <p>Circuit breakers, fuses, electric motors, sensors and electrical components — supply for industrial, commercial and institutional requirements.</p>
        <div class="exp-items"><span>Circuit Breakers</span><span>Fuses</span><span>Motors</span><span>Sensors</span><span>Components</span></div>
        <a href="category.php?slug=industrial-electrical" class="btn btn-primary">Explore Industrial Products</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ SECTION 13: PROMOTIONS ============ -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="sec-head reveal"><span class="sec-eyebrow">Offers</span><h2>Current Promotions</h2><p>Configured by our team — genuine offers only, updated regularly.</p></div>
    <div class="promo-grid">
      <a href="shop.php?offers=1" class="promo reveal" style="background:linear-gradient(135deg,#0D3B66,#12548f)"><span class="tag">Seasonal</span><h4>Featured Category Offers</h4><p>Selected products at special prices.</p><span class="btn btn-gold btn-sm">Shop Offers</span></a>
      <a href="business.php" class="promo reveal" style="background:linear-gradient(135deg,#071A2F,#0D3B66)"><span class="tag">B2B</span><h4>Bulk Purchase Benefits</h4><p>Wholesale pricing for business customers.</p><span class="btn btn-primary btn-sm">Learn More</span></a>
      <a href="shop.php?sort=new" class="promo reveal" style="background:linear-gradient(135deg,#1c1405,#7a5a10)"><span class="tag">Fresh Arrivals</span><h4>New Arrivals</h4><p>The latest additions to our catalogue.</p><span class="btn btn-ghost btn-sm" style="background:#fff">Explore New</span></a>
    </div>
  </div>
</section>

<!-- ============ SECTION 14: REVIEWS ============ -->
<?php if ($reviews): ?>
<section class="section" style="background:#fff">
  <div class="container">
    <div class="sec-head reveal"><span class="sec-eyebrow">Testimonials</span><h2>What Our Customers Say</h2></div>
    <div class="rev-grid">
      <?php foreach ($reviews as $r): ?>
      <div class="rev-card reveal">
        <div class="stars"><?= str_repeat('★', max(1, min(5, $r['rating']))) ?></div>
        <p>"<?= e($r['review']) ?>"</p>
        <div class="rev-by"><span class="rev-av"><?= e(strtoupper(substr($r['name'], 0, 1))) ?></span><div><b><?= e($r['name']) ?></b><span><?= e($r['company']) ?></span></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ SECTION 15: BLOG ============ -->
<?php if ($blogs): ?>
<section class="section">
  <div class="container">
    <div class="sec-head reveal"><span class="sec-eyebrow">Knowledge Hub</span><h2>Guides &amp; Insights</h2></div>
    <div class="blog-grid">
      <?php foreach ($blogs as $b): ?>
      <a href="article.php?slug=<?= e($b['slug']) ?>" class="blog-card reveal">
        <?php if ($b['featured_image']): ?><img src="<?= e($b['featured_image']) ?>" alt="<?= e($b['title']) ?>" loading="lazy"><?php endif; ?>
        <div class="bc-body"><span class="cat"><?= e($b['cat_name'] ?: 'Article') ?></span><h4><?= e($b['title']) ?></h4><p><?= e($b['excerpt']) ?></p></div>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2.5rem"><a href="blog.php" class="btn btn-dark">View All Articles</a></div>
  </div>
</section>
<?php endif; ?>

<!-- ============ SECTION 16: NEWSLETTER ============ -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="newsletter reveal">
      <span class="sec-eyebrow" style="color:var(--gold2)">Newsletter</span>
      <h2 style="margin:.6rem 0">Stay Ahead of New Products &amp; Offers</h2>
      <p style="color:#a9bdd4">Product updates, wholesale news and trade insights — straight to your inbox.</p>
      <form class="nl-form" id="nlForm"><input type="email" id="nlEmail" placeholder="Your email address" required><button class="btn btn-gold" type="submit">Subscribe</button></form>
    </div>
  </div>
</section>

<!-- ============ SECTION 17: FINAL CTA ============ -->
<section class="section final-cta">
  <div class="container" style="position:relative">
    <span class="fc-orbits"><span style="top:8%;left:6%">WHOLESALE</span><span style="top:22%;right:8%;animation-delay:1.2s">IMPORT</span><span style="bottom:26%;left:12%;animation-delay:2s">EXPORT</span><span style="bottom:12%;right:14%;animation-delay:.6s">RETAIL</span></span>
    <div class="fc-sphere"></div>
    <span class="sec-eyebrow">Let's Trade</span>
    <h2>ONE TRADING PARTNER.<br><span style="background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent">MULTIPLE POSSIBILITIES.</span></h2>
    <p style="color:#a9bdd4;max-width:520px;margin:1rem auto 0">Industrial supply, food products, wholesale, import and export — all through one trusted trading house.</p>
    <div class="hero-ctas"><a href="shop.php" class="btn btn-primary">SHOP NOW</a><a href="bulk-quote.php" class="btn btn-gold">BULK ENQUIRY</a></div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
