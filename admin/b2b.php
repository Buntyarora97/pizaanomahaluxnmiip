<?php
require_once __DIR__ . '/includes/admin-header.php';
if (!empty($_GET['approve']) || !empty($_GET['reject'])) {
    $id = (int)($_GET['approve'] ?? $_GET['reject']);
    $st = !empty($_GET['approve']) ? 'approved' : 'rejected';
    $pdo->prepare('UPDATE customers SET b2b_status = ? WHERE id = ?')->execute([$st, $id]);
    flash('B2B application ' . $st . '.');
    redirect('b2b.php');
}
$pending = $pdo->query("SELECT * FROM customers WHERE b2b_status='pending'")->fetchAll();
$all = $pdo->query("SELECT * FROM customers WHERE is_business=1 ORDER BY b2b_status='pending' DESC, id DESC")->fetchAll();
?>
<div class="admin-top"><h1>B2B Approvals</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Pending Applications (<?= count($pending) ?>)</h3>
<table class="tbl"><tr><th>Customer</th><th>Company</th><th>GSTIN</th><th>Business Type</th><th></th></tr>
<?php foreach ($pending as $p): ?><tr><td><b><?= e($p['full_name']) ?></b><br><span class="muted"><?= e($p['email']) ?> · <?= e($p['phone']) ?></span></td><td><?= e($p['company_name']) ?></td><td><?= e($p['gstin']) ?></td><td><?= e($p['business_type']) ?></td>
<td><a href="?approve=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Approve</a> <a href="?reject=<?= $p['id'] ?>" class="btn btn-ghost btn-sm" style="color:#c0392b">Reject</a></td></tr><?php endforeach; ?>
<?php if (!$pending): ?><tr><td colspan="5" class="muted">No pending applications.</td></tr><?php endif; ?></table></div>
<div class="acct-card"><h3>All Business Accounts</h3>
<table class="tbl"><tr><th>Customer</th><th>Company</th><th>GSTIN</th><th>Status</th></tr>
<?php foreach ($all as $a): ?><tr><td><?= e($a['full_name']) ?></td><td><?= e($a['company_name']) ?></td><td><?= e($a['gstin']) ?></td><td><span class="badge <?= e($a['b2b_status']) ?>"><?= e($a['b2b_status']) ?></span></td></tr><?php endforeach; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
