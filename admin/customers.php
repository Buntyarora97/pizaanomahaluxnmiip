<?php
require_once __DIR__ . '/includes/admin-header.php';
$customers = $pdo->query('SELECT c.*, (SELECT COUNT(*) FROM orders WHERE customer_id=c.id) AS orders_count FROM customers c ORDER BY c.id DESC')->fetchAll();
?>
<div class="admin-top"><h1>Customers (<?= count($customers) ?>)</h1></div>
<div class="acct-card"><table class="tbl"><tr><th>Name</th><th>Email</th><th>Phone</th><th>Company</th><th>Orders</th><th>B2B</th><th>Joined</th></tr>
<?php foreach ($customers as $c): ?><tr><td><b><?= e($c['full_name']) ?></b></td><td><?= e($c['email']) ?></td><td><?= e($c['phone']) ?></td><td><?= e($c['company_name']) ?></td><td><?= $c['orders_count'] ?></td><td><span class="badge <?= e($c['b2b_status']) ?>"><?= e($c['b2b_status']) ?></span></td><td><?= e(date('d M Y', strtotime($c['created_at']))) ?></td></tr><?php endforeach; ?>
<?php if (!$customers): ?><tr><td colspan="7" class="muted">No customers yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
