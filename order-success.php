<?php
require_once __DIR__ . '/includes/functions.php';
$onum = $_GET['order'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_number = ?');
$stmt->execute([$onum]);
$o = $stmt->fetch();
if (!$o) redirect('index.php');
$items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$items->execute([$o['id']]);
$items = $items->fetchAll();
$pageTitle = 'Order Confirmed — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container" style="max-width:760px">
  <div class="acct-card" style="text-align:center;padding:3rem">
    <div style="width:76px;height:76px;border-radius:50%;background:#e6f7ee;color:#0d9455;display:grid;place-items:center;margin:0 auto 1.2rem;font-size:2rem">✓</div>
    <span class="sec-eyebrow">Order Placed</span>
    <h1 style="color:var(--navy);margin:.6rem 0">THANK YOU FOR YOUR ORDER.</h1>
    <p class="muted">Order <b style="color:var(--navy2)"><?= e($o['order_number']) ?></b> · <?= money($o['grand_total']) ?> · Payment: <?= e(strtoupper($o['payment_method'])) ?></p>
  </div>
  <div class="acct-card">
    <h3>Order Items</h3>
    <?php foreach ($items as $i): ?><div class="sum-row"><span><?= e($i['product_name']) ?> × <?= $i['quantity'] ?></span><span><?= money($i['price']*$i['quantity']) ?></span></div><?php endforeach; ?>
    <div class="sum-row total"><span>Total</span><span><?= money($o['grand_total']) ?></span></div>
    <p class="muted" style="margin-top:1rem;font-size:.85rem"><b>Shipping to:</b> <?= nl2br(e($o['shipping_address'])) ?></p>
  </div>
  <div style="display:flex;gap:.8rem;justify-content:center;margin-top:1.4rem">
    <a href="track-order.php?order=<?= e($o['order_number']) ?>" class="btn btn-primary">Track Order</a>
    <a href="shop.php" class="btn btn-ghost">Continue Shopping</a>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
