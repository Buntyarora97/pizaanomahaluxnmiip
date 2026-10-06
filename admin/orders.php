<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && adminCan('orders.update')) {
    csrf_verify();
    $oid = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $pay = $_POST['payment_status'];
    $pdo->prepare('UPDATE orders SET status=?, payment_status=?, courier_name=?, tracking_number=?, tracking_url=? WHERE id=?')
        ->execute([$status, $pay, trim($_POST['courier_name'] ?? '') ?: null, trim($_POST['tracking_number'] ?? '') ?: null, trim($_POST['tracking_url'] ?? '') ?: null, $oid]);
    $pdo->prepare('INSERT INTO order_status_history (order_id, status, created_by) VALUES (?,?,?)')->execute([$oid, $status, $_SESSION['admin']['id']]);
    // Restore stock on cancel
    if ($status === 'cancelled') {
        $items = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?'); $items->execute([$oid]);
        foreach ($items as $it) {
            $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?')->execute([$it['quantity'], $it['product_id']]);
            $pdo->prepare("INSERT INTO inventory_movements (product_id, stock_change, reason, reference) VALUES (?,?,?,?)")->execute([$it['product_id'], $it['quantity'], 'cancel', 'ORDER-'.$oid]);
        }
    }
    flash('Order updated.');
    redirect('orders.php?view=' . $oid);
}
$view = null;
if (!empty($_GET['view'])) {
    $s = $pdo->prepare('SELECT o.*, c.full_name, c.email AS cemail FROM orders o LEFT JOIN customers c ON c.id=o.customer_id WHERE o.id = ?');
    $s->execute([(int)$_GET['view']]); $view = $s->fetch();
    if ($view) { $i = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?'); $i->execute([$view['id']]); $items = $i->fetchAll();
      $h = $pdo->prepare('SELECT * FROM order_status_history WHERE order_id = ? ORDER BY id'); $h->execute([$view['id']]); $history = $h->fetchAll(); }
}
$statuses = ['placed','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled'];
$orders = $pdo->query('SELECT o.*, c.full_name FROM orders o LEFT JOIN customers c ON c.id=o.customer_id ORDER BY o.id DESC LIMIT 100')->fetchAll();
?>
<div class="admin-top"><h1>Orders (<?= count($orders) ?>)</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<?php if ($view): ?>
<div class="acct-card">
  <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem">
    <div><h3><?= e($view['order_number']) ?></h3><span class="muted"><?= e(date('d M Y h:i A', strtotime($view['placed_at']))) ?> · <?= e($view['full_name'] ?: $view['guest_email'] ?: 'Guest') ?></span></div>
    <div><span class="badge <?= e($view['payment_status']) ?>"><?= e($view['payment_status']) ?></span> <span class="badge <?= e($view['status']) ?>"><?= e($view['status']) ?></span></div>
  </div>
  <table class="tbl" style="margin-top:1rem"><tr><th>Item</th><th>SKU</th><th>Qty</th><th>Rate</th><th>Amount</th></tr>
  <?php foreach ($items as $i): ?><tr><td><?= e($i['product_name']) ?></td><td><?= e($i['sku']) ?></td><td><?= $i['quantity'] ?></td><td><?= money($i['price']) ?></td><td><?= money($i['price']*$i['quantity']) ?></td></tr><?php endforeach; ?>
  <tr><td colspan="4" style="text-align:right"><b>Subtotal</b></td><td><?= money($view['subtotal']) ?></td></tr>
  <tr><td colspan="4" style="text-align:right"><b>Shipping</b></td><td><?= money($view['shipping_total']) ?></td></tr>
  <tr><td colspan="4" style="text-align:right"><b>Grand Total</b></td><td><b><?= money($view['grand_total']) ?></b></td></tr></table>
  <p class="muted" style="margin-top:1rem;font-size:.85rem"><b>Address:</b> <?= nl2br(e($view['shipping_address'])) ?><br><b>Payment:</b> <?= e($view['payment_method']) ?></p>
  <form method="post" style="margin-top:1.4rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.8rem;align-items:end"><?= csrf_field() ?>
    <input type="hidden" name="order_id" value="<?= $view['id'] ?>">
    <div class="field"><label>Status</label><select name="status"><?php foreach ($statuses as $s): ?><option <?= $view['status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Payment</label><select name="payment_status"><?php foreach (['pending','paid','failed','refunded','partial_refund'] as $s): ?><option <?= $view['payment_status']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Courier</label><input name="courier_name" value="<?= e($view['courier_name'] ?? '') ?>"></div>
    <div class="field"><label>Tracking No.</label><input name="tracking_number" value="<?= e($view['tracking_number'] ?? '') ?>"></div>
    <div class="field"><label>Tracking URL</label><input name="tracking_url" value="<?= e($view['tracking_url'] ?? '') ?>"></div>
    <button class="btn btn-primary">Update Order</button>
  </form>
  <h3 style="margin-top:1.6rem">Status History</h3>
  <p class="muted" style="font-size:.85rem"><?php foreach ($history as $h): ?><span class="badge placed"><?= e($h['status']) ?></span> <?php endforeach; ?></p>
</div>
<?php endif; ?>
<div class="acct-card"><table class="tbl"><tr><th>Order #</th><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr>
<?php foreach ($orders as $o): ?><tr><td><b><?= e($o['order_number']) ?></b></td><td><?= e(date('d M Y', strtotime($o['placed_at']))) ?></td><td><?= e($o['full_name'] ?: $o['guest_email'] ?: 'Guest') ?></td><td><?= money($o['grand_total']) ?></td><td><span class="badge <?= e($o['payment_status']) ?>"><?= e($o['payment_status']) ?></span></td><td><span class="badge <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td><td><a class="btn btn-ghost btn-sm" href="orders.php?view=<?= $o['id'] ?>">Manage</a></td></tr><?php endforeach; ?>
<?php if (!$orders): ?><tr><td colspan="7" class="muted">No orders yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
