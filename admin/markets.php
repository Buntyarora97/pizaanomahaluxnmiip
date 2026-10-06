<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('INSERT INTO trade_markets (country, trade_type, products, description) VALUES (?,?,?,?)')
        ->execute([trim($_POST['country']), $_POST['trade_type'], trim($_POST['products'] ?? '') ?: null, trim($_POST['description'] ?? '') ?: null]);
    flash('Saved successfully.');
    redirect(basename($_SERVER['PHP_SELF']));
}
if (isset($_GET['toggle'])) {
    $pdo->prepare('UPDATE trade_markets SET status = 1 - status WHERE id = ?')->execute([(int)$_GET['toggle']]);
    redirect(basename($_SERVER['PHP_SELF']));
}
if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM trade_markets WHERE id = ?')->execute([(int)$_GET['delete']]);
    flash('Deleted.'); redirect(basename($_SERVER['PHP_SELF']));
}
$rows = $pdo->query( + table_sql + r)->fetchAll();
?>
<div class="admin-top"><h1>Trade Markets</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Add New</h3>
<form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.8rem;align-items:end"><?= csrf_field() ?>
<div class="field"><label>Country *</label><input name="country" required></div><div class="field"><label>Type</label><select name="trade_type"><option value="export">Export</option><option value="import">Import</option><option value="both">Both</option></select></div><div class="field"><label>Products</label><input name="products"></div><div class="field"><label>Description</label><input name="description"></div>
<button class="btn btn-primary">Save</button></form>
<p class="muted" style="font-size:.78rem;margin-top:.6rem">Only verified markets should be published — they appear on the homepage global trade map.</p></div>
<div class="acct-card"><table class="tbl"><tr><th>Country</th><th>Type</th><th>Products</th><th>Description</th><th>Status</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><b><?= e($r['country']) ?></b></td><td><span class="badge placed"><?= e($r['trade_type']) ?></span></td><td class="muted"><?= e($r['products']) ?></td><td class="muted" style="max-width:220px"><?= e($r['description']) ?></td><td><span class="badge <?= $r['status']?'delivered':'cancelled' ?>"><?= $r['status']?'Shown':'Hidden' ?></span></td><td><a class="btn btn-ghost btn-sm" href="?toggle=<?= $r['id'] ?>">Toggle</a> <a class="btn btn-ghost btn-sm" style="color:#c0392b" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')">✕</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="9" class="muted">No records yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
