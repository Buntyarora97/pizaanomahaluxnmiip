<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pid = (int)$_POST['product_id']; $change = (int)$_POST['change'];
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE products SET stock = GREATEST(0, stock + ?) WHERE id = ?')->execute([$change, $pid]);
    $pdo->prepare("INSERT INTO inventory_movements (product_id, stock_change, reason, note) VALUES (?,?, 'adjustment', ?)")->execute([$pid, $change, trim($_POST['note'] ?? '')]);
    $pdo->commit();
    flash('Stock adjusted & logged.'); redirect('inventory.php');
}
$products = $pdo->query("SELECT id, name, sku, stock, low_stock_threshold FROM products WHERE status=1 ORDER BY stock ASC")->fetchAll();
$movements = $pdo->query('SELECT m.*, p.name FROM inventory_movements m JOIN products p ON p.id=m.product_id ORDER BY m.id DESC LIMIT 30')->fetchAll();
?>
<div class="admin-top"><h1>Inventory</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Adjust Stock</h3>
<form method="post" style="display:grid;grid-template-columns:2fr 1fr 2fr auto;gap:.8rem;align-items:end"><?= csrf_field() ?>
  <div class="field"><label>Product</label><select name="product_id"><?php foreach ($products as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= $p['stock'] ?>)</option><?php endforeach; ?></select></div>
  <div class="field"><label>Change (+/−)</label><input type="number" name="change" required></div>
  <div class="field"><label>Note</label><input name="note" placeholder="Reason for adjustment"></div>
  <button class="btn btn-primary">Apply</button>
</form></div>
<div class="acct-card"><h3>Stock Levels</h3>
<table class="tbl"><tr><th>Product</th><th>SKU</th><th>Stock</th><th>Low Alert At</th><th>Status</th></tr>
<?php foreach ($products as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= e($p['sku']) ?></td><td><b style="color:<?= $p['stock'] <= $p['low_stock_threshold'] ? '#c0392b' : '#0d9455' ?>"><?= $p['stock'] ?></b></td><td><?= $p['low_stock_threshold'] ?></td><td><?= $p['stock'] <= $p['low_stock_threshold'] ? '<span class="badge pending">LOW</span>' : '<span class="badge delivered">OK</span>' ?></td></tr><?php endforeach; ?></table></div>
<div class="acct-card"><h3>Recent Movements</h3>
<table class="tbl"><tr><th>Product</th><th>Change</th><th>Reason</th><th>Reference</th><th>Date</th></tr>
<?php foreach ($movements as $m): ?><tr><td><?= e($m['name']) ?></td><td><b style="color:<?= $m['stock_change']>0?'#0d9455':'#c0392b' ?>"><?= $m['stock_change']>0?'+':'' ?><?= $m['stock_change'] ?></b></td><td><?= e($m['reason']) ?></td><td><?= e($m['reference'] ?: $m['note']) ?></td><td><?= e(date('d M Y', strtotime($m['created_at']))) ?></td></tr><?php endforeach; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
