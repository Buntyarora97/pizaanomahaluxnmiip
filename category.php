<?php
require_once __DIR__ . '/includes/functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ? AND status = 1');
$stmt->execute([$slug]);
$cat = $stmt->fetch();
if (!$cat) { http_response_code(404); include __DIR__ . '/404.php'; exit; }
$pageTitle = $cat['name'] . ' — ' . setting('site_name');
$pageDesc = $cat['description'] ?? $pageTitle;
$canonical = SITE_URL . '/category/' . $cat['slug'];
$stmt = $pdo->prepare("SELECT p.*, b.name AS brand_name,
  (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS img
  FROM products p LEFT JOIN brands b ON b.id=p.brand_id
  WHERE p.category_id = ? AND p.status=1 ORDER BY p.is_featured DESC, p.id DESC");
$stmt->execute([$cat['id']]);
$products = $stmt->fetchAll();
$subs = $pdo->prepare('SELECT * FROM categories WHERE parent_id = ? AND status=1');
$subs->execute([$cat['id']]);
$subcats = $subs->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container">
  <div class="breadcrumb"><a href="index.php">Home</a> / <a href="shop.php">Shop</a> / <?= e($cat['name']) ?></div>
  <h1><?= e($cat['name']) ?></h1><p><?= e($cat['description']) ?></p>
</div></section>
<?php if ($subcats): ?>
<section style="padding:2rem 0 0"><div class="container" style="display:flex;gap:.6rem;flex-wrap:wrap">
  <?php foreach ($subcats as $s): ?><a href="category.php?slug=<?= e($s['slug']) ?>" class="chip" style="background:#fff;color:var(--navy);border-color:var(--line)"><?= e($s['name']) ?></a><?php endforeach; ?>
</div></section>
<?php endif; ?>
<section class="section"><div class="container">
<?php if (!$products): ?><p class="muted" style="text-align:center;padding:3rem 0">Products are being added to this category. Please check back soon or <a href="bulk-quote.php" style="color:var(--navy2);font-weight:600">send an enquiry</a>.</p>
<?php else: ?><div class="p-grid">
  <?php foreach ($products as $p): $off = ($p['mrp'] && $p['mrp'] > $p['price']) ? round((1-$p['price']/$p['mrp'])*100) : 0; ?>
  <div class="p-card reveal">
    <div class="p-img">
      <?php if ($off): ?><span class="p-badges"><span class="p-badge off">-<?= $off ?>%</span></span><?php endif; ?>
      <a href="product.php?slug=<?= e($p['slug']) ?>"><img src="<?= e(productImage($p['img'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a>
      <div class="p-actions">
        <button class="pa-btn js-wish" data-id="<?= $p['id'] ?>"><svg class="ic"><use href="#i-heart"/></svg></button>
        <button class="pa-btn js-compare" data-id="<?= $p['id'] ?>"><svg class="ic"><use href="#i-compare"/></svg></button>
      </div>
    </div>
    <div class="p-body">
      <span class="p-brand"><?= e($p['brand_name'] ?: $cat['name']) ?></span>
      <span class="p-name"><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></span>
      <div class="p-price">
        <?php if ($p['quote_only']): ?><span class="now" style="font-size:.92rem">Request Quote</span>
        <?php else: ?><span class="now"><?= money($p['price']) ?></span>
          <?php if ($p['mrp'] > $p['price']): ?><span class="mrp"><?= money($p['mrp']) ?></span><span class="off"><?= $off ?>% off</span><?php endif; ?>
        <?php endif; ?>
      </div>
      <span class="p-stock <?= $p['stock']>0?'in':'out' ?>"><?= $p['stock']>0?'● In Stock':'● Out of Stock' ?></span>
      <?php if ($p['stock']>0 && $p['purchasable'] && !$p['quote_only']): ?>
        <button class="btn btn-primary btn-sm js-add-cart" data-id="<?= $p['id'] ?>">Add to Cart</button>
      <?php else: ?><a href="bulk-quote.php?product=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Request Quote</a><?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div><?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
