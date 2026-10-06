<?php
require_once __DIR__ . '/../includes/functions.php';
$ids = array_filter(array_map('intval', explode(',', $_GET['ids'] ?? '')));
$ids = array_slice($ids, 0, 4);
if (!$ids) { echo '<p class="muted">No products selected.</p>'; exit; }
$in = implode(',', $ids);
$prods = $pdo->query("SELECT p.*, b.name AS brand_name, (SELECT image FROM product_images WHERE product_id=p.id LIMIT 1) AS img FROM products p LEFT JOIN brands b ON b.id=p.brand_id WHERE p.id IN ($in) AND p.status=1")->fetchAll();
if (!$prods) { echo '<p class="muted">Products not found.</p>'; exit; }
echo '<table class="cmp-table"><tr><th>Product</th>';
foreach ($prods as $p) echo '<td><a href="product.php?slug=' . e($p['slug']) . '"><b>' . e($p['name']) . '</b></a></td>';
echo '</tr><tr><th>Image</th>';
foreach ($prods as $p) echo '<td><img src="' . e(productImage($p['img'])) . '" style="max-width:140px"></td>';
echo '</tr><tr><th>Price</th>';
foreach ($prods as $p) echo '<td><b>' . ($p['quote_only'] ? 'Quote' : money($p['price'])) . '</b></td>';
echo '</tr><tr><th>Brand</th>';
foreach ($prods as $p) echo '<td>' . e($p['brand_name'] ?: '—') . '</td>';
echo '</tr><tr><th>SKU</th>';
foreach ($prods as $p) echo '<td>' . e($p['sku']) . '</td>';
echo '</tr><tr><th>Stock</th>';
foreach ($prods as $p) echo '<td>' . ($p['stock'] > 0 ? 'In Stock (' . $p['stock'] . ')' : 'Out of Stock') . '</td>';
echo '</tr><tr><th>Buy</th>';
foreach ($prods as $p) echo '<td>' . ($p['stock'] > 0 && $p['purchasable'] && !$p['quote_only'] ? '<button class="btn btn-primary btn-sm js-add-cart" data-id="' . $p['id'] . '">Add to Cart</button>' : '<a class="btn btn-ghost btn-sm" href="bulk-quote.php?product=' . $p['id'] . '">Quote</a>') . '</td>';
echo '</tr></table>';
