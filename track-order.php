<?php
require_once __DIR__ . '/includes/functions.php';
$o = null; $history = [];
if (!empty($_GET['order'])) {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = ?');
    $stmt->execute([trim($_GET['order'])]);
    $o = $stmt->fetch();
    if ($o) {
        $h = $pdo->prepare('SELECT * FROM order_status_history WHERE order_id = ? ORDER BY id');
        $h->execute([$o['id']]); $history = array_column($h->fetchAll(), 'status');
        $items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?'); $items->execute([$o['id']]); $items = $items->fetchAll();
    }
}
$stages = ['placed','confirmed','processing','packed','shipped','out_for_delivery','delivered'];
$pageTitle = 'Track Order — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="index.php">Home</a> / Track Order</div><h1>Track Your Order</h1><p>Enter your order number to see live status.</p></div></section>
<section class="section"><div class="container" style="max-width:760px">
  <form class="form-card" method="get" style="display:flex;gap:.7rem;max-width:none">
    <input name="order" placeholder="Order number e.g. MTH-2026-000001" value="<?= e($_GET['order'] ?? '') ?>" required style="flex:1;border:1.5px solid var(--line);border-radius:12px;padding:.85rem 1rem">
    <button class="btn btn-primary">Track</button>
  </form>
  <?php if (!empty($_GET['order'])): if (!$o): ?>
    <div class="alert error" style="max-width:640px;margin:2rem auto 0">Order not found. Please check the order number.</div>
  <?php else: ?>
  <div class="acct-card" style="margin-top:2rem">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem">
      <div><b style="color:var(--navy)"><?= e($o['order_number']) ?></b><br><span class="muted"><?= e(date('d M Y, h:i A', strtotime($o['placed_at']))) ?></span></div>
      <div><span class="badge <?= e($o['payment_status']) ?>"><?= e(ucfirst($o['payment_status'])) ?></span> <span class="badge <?= e($o['status']) ?>"><?= e(ucwords(str_replace('_',' ',$o['status']))) ?></span></div>
    </div>
    <div class="timeline">
      <?php foreach ($stages as $s): $done = in_array($s, $history) || array_search($o['status'], $stages) >= array_search($s, $stages); ?>
      <div class="tl-step <?= $done ? 'done' : '' ?>"><span><?= e(ucwords(str_replace('_', ' ', $s))) ?></span></div>
      <?php endforeach; ?>
    </div>
    <?php if ($o['tracking_number']): ?>
      <p style="margin-top:1rem;font-size:.9rem"><b>Courier:</b> <?= e($o['courier_name']) ?> · <b>Tracking:</b> <?= e($o['tracking_number']) ?>
      <?php if ($o['tracking_url']): ?>· <a href="<?= e($o['tracking_url']) ?>" target="_blank" rel="noopener" style="color:var(--navy2);font-weight:600">Track on courier site →</a><?php endif; ?></p>
    <?php endif; ?>
    <div style="margin-top:1.4rem">
      <?php foreach ($items as $i): ?><div class="sum-row"><span><?= e($i['product_name']) ?> × <?= $i['quantity'] ?></span><span><?= money($i['price']*$i['quantity']) ?></span></div><?php endforeach; ?>
      <div class="sum-row total"><span>Total</span><span><?= money($o['grand_total']) ?></span></div>
    </div>
  </div>
  <?php endif; endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
