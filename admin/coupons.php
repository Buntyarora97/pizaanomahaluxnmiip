<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('INSERT INTO coupons (code, type, value, min_cart, max_discount, starts_at, expires_at, usage_limit) VALUES (?,?,?,?,?,?,?,?)')
        ->execute([strtoupper(trim($_POST['code'])), $_POST['type'], (float)$_POST['value'], (float)$_POST['min_cart'] ?: 0, $_POST['max_discount'] !== '' ? (float)$_POST['max_discount'] : null, $_POST['starts_at'] ?: null, $_POST['expires_at'] ?: null, $_POST['usage_limit'] ?: null]);
    flash('Coupon created.'); redirect('coupons.php');
}
if (isset($_GET['toggle'])) { $pdo->prepare('UPDATE coupons SET status = 1 - status WHERE id = ?')->execute([(int)$_GET['toggle']]); redirect('coupons.php'); }
$coupons = $pdo->query('SELECT * FROM coupons ORDER BY id DESC')->fetchAll();
?>
<div class="admin-top"><h1>Coupons</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Create Coupon</h3>
<form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.8rem;align-items:end"><?= csrf_field() ?>
  <div class="field"><label>Code *</label><input name="code" required style="text-transform:uppercase"></div>
  <div class="field"><label>Type</label><select name="type"><option value="percent">Percentage %</option><option value="fixed">Fixed ₹</option></select></div>
  <div class="field"><label>Value *</label><input type="number" step="0.01" name="value" required></div>
  <div class="field"><label>Min Cart ₹</label><input type="number" name="min_cart" value="0"></div>
  <div class="field"><label>Max Discount ₹</label><input type="number" name="max_discount"></div>
  <div class="field"><label>Starts</label><input type="date" name="starts_at"></div>
  <div class="field"><label>Expires</label><input type="date" name="expires_at"></div>
  <div class="field"><label>Usage Limit</label><input type="number" name="usage_limit"></div>
  <button class="btn btn-primary">Create</button>
</form></div>
<div class="acct-card"><h3>All Coupons</h3>
<table class="tbl"><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Cart</th><th>Expiry</th><th>Status</th><th></th></tr>
<?php foreach ($coupons as $c): ?><tr><td><b><?= e($c['code']) ?></b></td><td><?= e($c['type']) ?></td><td><?= $c['type']==='percent' ? $c['value'].'%' : money($c['value']) ?></td><td><?= money($c['min_cart']) ?></td><td><?= e($c['expires_at'] ?: '—') ?></td><td><span class="badge <?= $c['status']?'delivered':'cancelled' ?>"><?= $c['status']?'Active':'Disabled' ?></span></td><td><a class="btn btn-ghost btn-sm" href="?toggle=<?= $c['id'] ?>"><?= $c['status']?'Disable':'Enable' ?></a></td></tr><?php endforeach; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
