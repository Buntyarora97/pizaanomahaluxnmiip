<?php
require_once __DIR__ . '/includes/admin-header.php';
$todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(placed_at)=CURDATE()")->fetchColumn();
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE DATE(placed_at)=CURDATE() AND payment_status IN ('paid','pending')")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('placed','confirmed','processing')")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE status=1 AND stock <= low_stock_threshold")->fetchColumn();
$customers = $pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$quotes = $pdo->query("SELECT COUNT(*) FROM bulk_quotes WHERE status='new'")->fetchColumn();
$recent = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
$lowProducts = $pdo->query('SELECT name, sku, stock FROM products WHERE status=1 AND stock <= low_stock_threshold ORDER BY stock ASC LIMIT 8')->fetchAll();
$b2bPending = $pdo->query("SELECT * FROM customers WHERE b2b_status='pending' LIMIT 8")->fetchAll();
$monthly = $pdo->query("SELECT DATE_FORMAT(placed_at,'%b') m, SUM(grand_total) t FROM orders WHERE placed_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY YEAR(placed_at), MONTH(placed_at) ORDER BY placed_at")->fetchAll();
?>
<div class="admin-top"><h1>Dashboard</h1><a href="<?= SITE_URL ?>/" target="_blank" class="btn btn-ghost btn-sm">View Website →</a></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="a-cards">
  <div class="a-card c-cyan"><b><?= $todayOrders ?></b><span>Today's Orders</span></div>
  <div class="a-card c-gold"><b><?= money($todayRevenue) ?></b><span>Today's Revenue</span></div>
  <div class="a-card"><b><?= $pendingOrders ?></b><span>Pending Orders</span></div>
  <div class="a-card" style="background:#fff4e0"><b><?= $lowStock ?></b><span>Low Stock Alerts</span></div>
  <div class="a-card"><b><?= $customers ?></b><span>Customers</span></div>
  <div class="a-card c-cyan"><b><?= $quotes ?></b><span>New Bulk Quotes</span></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.4rem">
  <div class="acct-card">
    <h3>Recent Orders</h3>
    <table class="tbl"><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr>
    <?php foreach ($recent as $o): ?><tr><td><a href="orders.php?view=<?= $o['id'] ?>" style="color:var(--navy2);font-weight:600"><?= e($o['order_number']) ?></a></td><td><?= e($o['guest_email'] ?: ('#' . $o['customer_id'])) ?></td><td><?= money($o['grand_total']) ?></td><td><span class="badge <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td></tr><?php endforeach; ?>
    <?php if (!$recent): ?><tr><td colspan="4" class="muted">No orders yet.</td></tr><?php endif; ?></table>
  </div>
  <div class="acct-card">
    <h3>Sales Trend (6 months)</h3>
    <div style="display:flex;align-items:flex-end;gap:.6rem;height:180px;padding-top:1rem">
      <?php $max = max(1, max(array_column($monthly ?: [['t'=>1]], 't'))); foreach ($monthly as $m): $h = max(6, round($m['t'] / $max * 160)); ?>
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:.4rem"><div style="width:100%;height:<?= $h ?>px;background:linear-gradient(180deg,var(--cyan),var(--navy2));border-radius:8px 8px 0 0" title="<?= money($m['t']) ?>"></div><span style="font-size:.7rem;color:var(--muted)"><?= e($m['m']) ?></span></div>
      <?php endforeach; if (!$monthly): ?><p class="muted">No sales data yet.</p><?php endif; ?>
    </div>
    <h3 style="margin-top:1.8rem">Low Stock Products</h3>
    <table class="tbl"><?php foreach ($lowProducts as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= e($p['sku']) ?></td><td><b style="color:#c0392b"><?= $p['stock'] ?> left</b></td></tr><?php endforeach; ?>
    <?php if (!$lowProducts): ?><tr><td class="muted">All products well stocked.</td></tr><?php endif; ?></table>
  </div>
</div>
<?php if ($b2bPending): ?>
<div class="acct-card"><h3>Pending B2B Approvals</h3>
<table class="tbl"><tr><th>Customer</th><th>Company</th><th>GSTIN</th><th></th></tr>
<?php foreach ($b2bPending as $b): ?><tr><td><?= e($b['full_name']) ?></td><td><?= e($b['company_name']) ?></td><td><?= e($b['gstin']) ?></td><td><a href="b2b.php" class="btn btn-gold btn-sm">Review</a></td></tr><?php endforeach; ?></table></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
