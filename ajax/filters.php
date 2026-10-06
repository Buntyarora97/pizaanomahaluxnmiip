<?php
require_once __DIR__ . '/../includes/functions.php';
$cats = $_POST['cat'] ?? [];
$brands = $_POST['brand'] ?? [];
$min = $_POST['min'] !== '' ? (float)$_POST['min'] : null;
$max = $_POST['max'] !== '' ? (float)$_POST['max'] : null;
$stock = !empty($_POST['stock']);
$sort = $_POST['sort'] ?? 'featured';
$sql = "SELECT p.*, b.name AS brand_name, c.name AS cat_name,
  (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS img
  FROM products p LEFT JOIN brands b ON b.id=p.brand_id JOIN categories c ON c.id=p.category_id
  WHERE p.status=1";
$params = [];
if ($cats) { $sql .= ' AND p.category_id IN (' . implode(',', array_map('intval', $cats)) . ')'; }
if ($brands) { $sql .= ' AND p.brand_id IN (' . implode(',', array_map('intval', $brands)) . ')'; }
if ($min !== null) { $sql .= ' AND p.price >= ?'; $params[] = $min; }
if ($max !== null) { $sql .= ' AND p.price <= ?'; $params[] = $max; }
if ($stock) { $sql .= ' AND p.stock > 0'; }
$sql .= match ($sort) {
    'plh' => ' ORDER BY p.price ASC', 'phl' => ' ORDER BY p.price DESC',
    'new' => ' ORDER BY p.id DESC', 'name' => ' ORDER BY p.name ASC',
    default => ' ORDER BY p.is_featured DESC, p.id DESC'
};
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll();
echo '<!--count:' . count($rows) . '-->';
if (!$rows) { echo '<p class="muted" style="grid-column:1/-1;text-align:center;padding:3rem 0">No products match these filters.</p>'; exit; }
foreach ($rows as $p):
  $off = ($p['mrp'] && $p['mrp'] > $p['price']) ? round((1-$p['price']/$p['mrp'])*100) : 0; ?>
<div class="p-card">
  <div class="p-img">
    <?php if ($off): ?><span class="p-badges"><span class="p-badge off">-<?= $off ?>%</span></span><?php endif; ?>
    <a href="product.php?slug=<?= e($p['slug']) ?>"><img src="<?= e(productImage($p['img'])) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a>
    <div class="p-actions">
      <button class="pa-btn js-wish" data-id="<?= $p['id'] ?>"><svg class="ic"><use href="#i-heart"/></svg></button>
      <button class="pa-btn js-compare" data-id="<?= $p['id'] ?>"><svg class="ic"><use href="#i-compare"/></svg></button>
    </div>
  </div>
  <div class="p-body">
    <span class="p-brand"><?= e($p['brand_name'] ?: $p['cat_name']) ?></span>
    <span class="p-name"><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></span>
    <div class="p-price">
      <?php if ($p['quote_only']): ?><span class="now" style="font-size:.92rem">Request Quote</span>
      <?php else: ?><span class="now"><?= money($p['price']) ?></span>
        <?php if ($p['mrp'] > $p['price']): ?><span class="mrp"><?= money($p['mrp']) ?></span><?php endif; ?>
      <?php endif; ?>
    </div>
    <span class="p-stock <?= $p['stock']>0?'in':'out' ?>"><?= $p['stock']>0?'● In Stock':'● Out of Stock' ?></span>
    <?php if ($p['stock']>0 && $p['purchasable'] && !$p['quote_only']): ?>
      <button class="btn btn-primary btn-sm js-add-cart" data-id="<?= $p['id'] ?>">Add to Cart</button>
    <?php else: ?><a href="bulk-quote.php?product=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Request Quote</a><?php endif; ?>
  </div>
</div>
<?php endforeach;
