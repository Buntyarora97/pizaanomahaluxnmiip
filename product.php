<?php
require_once __DIR__ . '/includes/functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug, c.parent_id AS cat_parent, b.name AS brand_name,
  (SELECT percentage FROM tax_rates tr JOIN tax_classes tc ON tc.id=tr.tax_class_id WHERE tc.id=p.tax_class_id LIMIT 1) AS tax_pct
  FROM products p JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.brand_id
  WHERE p.slug = ? AND p.status = 1");
$stmt->execute([$slug]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); include __DIR__ . '/404.php'; exit; }
$pdo->prepare('UPDATE products SET views = views + 1 WHERE id = ?')->execute([$p['id']]);
$pageTitle = $p['name'] . ' — ' . setting('site_name');
$pageDesc = $p['short_description'] ?: $pageTitle;
$canonical = SITE_URL . '/product/' . $p['slug'];

$imgs = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order');
$imgs->execute([$p['id']]);
$images = $imgs->fetchAll();
$vars = $pdo->prepare('SELECT * FROM product_variants WHERE product_id = ? AND status=1');
$vars->execute([$p['id']]);
$variants = $vars->fetchAll();
$attrs = $pdo->prepare('SELECT a.name, pav.value FROM product_attribute_values pav JOIN attributes a ON a.id=pav.attribute_id WHERE pav.product_id = ?');
$attrs->execute([$p['id']]);
$attributes = $attrs->fetchAll();
$docs = $pdo->prepare('SELECT * FROM product_documents WHERE product_id = ?');
$docs->execute([$p['id']]);
$documents = $docs->fetchAll();
$revs = $pdo->prepare('SELECT r.*, c.full_name FROM reviews r JOIN customers c ON c.id=r.customer_id WHERE r.product_id = ? AND r.status=1 ORDER BY r.id DESC LIMIT 5');
$revs->execute([$p['id']]);
$reviews = $revs->fetchAll();
$avg = $pdo->prepare('SELECT AVG(rating), COUNT(*) FROM reviews WHERE product_id = ? AND status=1');
$avg->execute([$p['id']]);
list($avgRating, $revCount) = $avg->fetch(PDO::FETCH_NUM);
$related = $pdo->prepare("SELECT p.*, (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS img FROM products p WHERE p.category_id = ? AND p.id != ? AND p.status=1 LIMIT 4");
$related->execute([$p['category_id'], $p['id']]);
$related = $related->fetchAll();
$faqs = $pdo->query('SELECT * FROM faqs WHERE status=1 ORDER BY sort_order LIMIT 5')->fetchAll();
$off = ($p['mrp'] && $p['mrp'] > $p['price']) ? round((1 - $p['price'] / $p['mrp']) * 100) : 0;

// Schema (real data only)
$schema = ["@context"=>"https://schema.org","@type"=>"Product","name"=>$p['name'],"sku"=>$p['sku'],"description"=>strip_tags($p['short_description'] ?: $p['name']),"brand"=>["@type"=>"Brand","name"=>$p['brand_name'] ?: 'Mahaluxmi Trading'],"offers"=>["@type"=>"Offer","priceCurrency"=>"INR","price"=>$p['price'],"availability"=>$p['stock']>0?"https://schema.org/InStock":"https://schema.org/OutOfStock","url"=>$canonical]];
if ($images) $schema['image'] = productImage($images[0]['image']);
if ($revCount > 0) $schema['aggregateRating'] = ["@type"=>"AggregateRating","ratingValue"=>round($avgRating,1),"reviewCount"=>$revCount];
include __DIR__ . '/includes/header.php';
?>
<script type="application/ld+json"><?= json_encode($schema) ?></script>
<section class="section"><div class="container">
  <div class="breadcrumb"><a href="index.php">Home</a> / <a href="category.php?slug=<?= e($p['cat_slug']) ?>"><?= e($p['cat_name']) ?></a> / <?= e($p['name']) ?></div>
  <div class="pdp">
    <div class="pdp-gallery">
      <div class="pdp-main"><img id="pdpImg" src="<?= e(productImage($images[0]['image'] ?? null)) ?>" alt="<?= e($p['name']) ?>"></div>
      <?php if (count($images) > 1): ?><div class="pdp-thumbs">
        <?php foreach ($images as $i => $im): ?><img src="<?= e(productImage($im['image'])) ?>" data-full="<?= e(productImage($im['image'])) ?>" class="<?= $i===0?'active':'' ?>" alt="<?= e($im['alt_text'] ?: $p['name']) ?>"><?php endforeach; ?>
      </div><?php endif; ?>
    </div>
    <div class="pdp-info">
      <span class="p-brand"><?= e($p['brand_name'] ?: $p['cat_name']) ?></span>
      <h1><?= e($p['name']) ?></h1>
      <div class="pdp-meta">
        <span>SKU: <?= e($p['sku']) ?></span>
        <?php if ($revCount): ?><span>★ <?= number_format($avgRating,1) ?> (<?= $revCount ?> review<?= $revCount>1?'s':'' ?>)</span><?php endif; ?>
        <span>Category: <a href="category.php?slug=<?= e($p['cat_slug']) ?>" style="color:var(--navy2)"><?= e($p['cat_name']) ?></a></span>
      </div>
      <?php if ($p['short_description']): ?><p class="muted" style="line-height:1.7"><?= e($p['short_description']) ?></p><?php endif; ?>
      <div class="pdp-price">
        <?php if (!$p['quote_only']): ?>
          <span class="now"><?= money($p['price']) ?></span>
          <?php if ($p['mrp'] > $p['price']): ?><span class="mrp"><?= money($p['mrp']) ?></span><span class="off"><?= $off ?>% OFF</span><?php endif; ?>
        <?php else: ?><span class="now" style="font-size:1.3rem">Price on Request</span><?php endif; ?>
      </div>
      <p class="tax-note"><?= $p['tax_pct'] ? 'Inclusive of all taxes' : 'Taxes calculated at checkout' ?><?= $p['hsn'] ? ' · HSN: ' . e($p['hsn']) : '' ?></p>
      <p class="p-stock <?= $p['stock']>0?'in':'out' ?>" style="margin:.6rem 0"><?= $p['stock']>0 ? '● In Stock ('.$p['stock'].' available)' : '● Out of Stock' ?></p>

      <?php if ($variants): ?>
      <h5 style="margin-top:1.2rem">Select Variant</h5>
      <div class="variant-pills">
        <?php foreach ($variants as $v): ?>
        <label><input type="radio" name="variant" value="<?= $v['id'] ?>" <?= $v['stock']<=0?'disabled':'' ?>><span><?= e($v['variant_name']) ?> — <?= money($v['price']) ?></span></label>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($p['stock'] > 0 && $p['purchasable'] && !$p['quote_only']): ?>
      <div class="pdp-ctas">
        <span class="qty"><button class="minus">−</button><input id="qtyInput" value="1" inputmode="numeric"><button class="plus">+</button></span>
        <button class="btn btn-dark js-add-cart" data-id="<?= $p['id'] ?>">ADD TO CART</button>
        <button class="btn btn-primary js-add-cart" data-id="<?= $p['id'] ?>" data-buy="1">BUY NOW</button>
      </div>
      <?php endif; ?>
      <div class="pdp-ctas" style="margin-top:0">
        <button class="btn btn-ghost btn-sm js-wish" data-id="<?= $p['id'] ?>"><svg class="ic"><use href="#i-heart"/></svg> Wishlist</button>
        <button class="btn btn-ghost btn-sm js-compare" data-id="<?= $p['id'] ?>"><svg class="ic"><use href="#i-compare"/></svg> Compare</button>
        <a class="btn btn-gold btn-sm" href="bulk-quote.php?product=<?= $p['id'] ?>">BULK QUOTE</a>
        <?php if (setting('whatsapp')): ?>
        <a class="btn btn-ghost btn-sm" target="_blank" rel="noopener" href="https://wa.me/<?= preg_replace('/\D/','',setting('whatsapp')) ?>?text=<?= urlencode('Hello Mahaluxmi Trading House, I am interested in '.$p['name'].', SKU '.$p['sku'].'. Please share more details.') ?>"><svg class="ic"><use href="#i-wa"/></svg> WhatsApp</a>
        <?php endif; ?>
      </div>
      <?php if ($p['stock'] <= 0): ?>
      <form method="post" action="ajax/notify-stock.php" style="display:flex;gap:.5rem;margin-top:1rem;max-width:360px">
        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
        <input type="email" name="email" required placeholder="Email for stock alert" style="flex:1;border:1.5px solid var(--line);border-radius:10px;padding:.6rem .9rem">
        <button class="btn btn-ghost btn-sm">Notify Me</button>
      </form>
      <?php endif; ?>

      <div class="pdp-tabs">
        <div class="tab-btns">
          <button class="active" data-tab="tab-desc">Description</button>
          <?php if ($attributes): ?><button data-tab="tab-spec">Specifications</button><?php endif; ?>
          <?php if ($documents): ?><button data-tab="tab-docs">Documents</button><?php endif; ?>
          <button data-tab="tab-rev">Reviews<?= $revCount ? " ($revCount)" : '' ?></button>
          <?php if ($faqs): ?><button data-tab="tab-faq">FAQs</button><?php endif; ?>
        </div>
        <div class="tab-pane active" id="tab-desc"><?= nl2br(e($p['description'] ?: $p['short_description'] ?: 'Details coming soon.')) ?></div>
        <?php if ($attributes): ?><div class="tab-pane" id="tab-spec"><table class="spec-table">
          <?php foreach ($attributes as $a): ?><tr><td><?= e($a['name']) ?></td><td><?= e($a['value']) ?></td></tr><?php endforeach; ?>
          <?php if ($p['weight']): ?><tr><td>Weight</td><td><?= e($p['weight']) ?> kg</td></tr><?php endif; ?>
          <?php if ($p['dimensions']): ?><tr><td>Dimensions</td><td><?= e($p['dimensions']) ?></td></tr><?php endif; ?>
        </table></div><?php endif; ?>
        <?php if ($documents): ?><div class="tab-pane" id="tab-docs"><ul style="list-style:none"><?php foreach ($documents as $d): ?><li style="padding:.5rem 0"><a href="<?= e(UPLOAD_URL.$d['file_path']) ?>" target="_blank" style="color:var(--navy2);font-weight:600">📄 <?= e($d['title']) ?></a></li><?php endforeach; ?></ul></div><?php endif; ?>
        <div class="tab-pane" id="tab-rev">
          <?php if ($reviews): foreach ($reviews as $r): ?>
            <div style="border-bottom:1px solid var(--line);padding:1rem 0">
              <div class="stars" style="margin:0">★ <?= $r['rating'] ?></div>
              <b><?= e($r['title'] ?: 'Review') ?></b> <?= $r['is_verified_purchase'] ? '<span class="badge delivered">Verified Purchase</span>' : '' ?>
              <p class="muted" style="margin-top:.3rem"><?= e($r['review']) ?></p><small class="muted">— <?= e($r['full_name']) ?></small>
            </div>
          <?php endforeach; else: ?><p>No reviews yet.<?php if(customer()): ?> Be the first to review after purchase.<?php endif; ?></p><?php endif; ?>
        </div>
        <?php if ($faqs): ?><div class="tab-pane" id="tab-faq">
          <?php foreach ($faqs as $f): ?><details style="padding:.7rem 0;border-bottom:1px solid var(--line)"><summary style="font-weight:600;color:var(--navy);cursor:pointer"><?= e($f['question']) ?></summary><p class="muted" style="margin-top:.5rem"><?= e($f['answer']) ?></p></details><?php endforeach; ?>
        </div><?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($related): ?>
  <div style="margin-top:4rem">
    <div class="sec-head"><span class="sec-eyebrow">Continue Exploring</span><h2>You May Also Like</h2></div>
    <div class="p-grid">
      <?php foreach ($related as $r): ?>
      <div class="p-card"><div class="p-img"><a href="product.php?slug=<?= e($r['slug']) ?>"><img src="<?= e(productImage($r['img'])) ?>" alt="<?= e($r['name']) ?>" loading="lazy"></a></div>
      <div class="p-body"><span class="p-name"><a href="product.php?slug=<?= e($r['slug']) ?>"><?= e($r['name']) ?></a></span>
      <?php if (!$r['quote_only']): ?><div class="p-price"><span class="now"><?= money($r['price']) ?></span></div><?php endif; ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
