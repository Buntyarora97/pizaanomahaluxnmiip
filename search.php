<?php
require_once __DIR__ . '/includes/functions.php';
$q = trim($_GET['q'] ?? '');
$results = [];
if ($q) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name,
      (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS img
      FROM products p JOIN categories c ON c.id=p.category_id
      WHERE p.status=1 AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?) ORDER BY p.is_featured DESC LIMIT 48");
    $like = "%$q%";
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll();
}
$pageTitle = "Search: $q — " . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container">
  <div class="sec-head"><span class="sec-eyebrow">Search</span><h2>Results for "<?= e($q) ?>"</h2><p><?= count($results) ?> product<?= count($results)!=1?'s':'' ?> found</p></div>
  <?php if (!$results): ?><p class="muted" style="text-align:center">No products match your search. <a href="bulk-quote.php" style="color:var(--navy2)">Send a bulk enquiry →</a></p>
  <?php else: ?><div class="p-grid">
    <?php foreach ($results as $p): ?><div class="p-card"><div class="p-img"><a href="product.php?slug=<?= e($p['slug']) ?>"><img src="<?= e(productImage($p['img'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a></div>
    <div class="p-body"><span class="p-brand"><?= e($p['cat_name']) ?></span><span class="p-name"><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></span>
    <?php if (!$p['quote_only']): ?><div class="p-price"><span class="now"><?= money($p['price']) ?></span></div><?php endif; ?></div></div>
    <?php endforeach; ?>
  </div><?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
