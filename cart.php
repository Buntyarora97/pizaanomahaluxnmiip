<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['item_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'remove') {
        $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND cart_id = ?')->execute([$id, getCartId()]);
    } elseif (($_POST['action'] ?? '') === 'update') {
        $qty = max(1, (int)$_POST['quantity']);
        $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?')->execute([$qty, $id, getCartId()]);
    }
    redirect('cart.php');
}
$tot = cartTotals();
$pageTitle = 'Shopping Cart — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container">
  <div class="sec-head"><span class="sec-eyebrow">Your Selection</span><h2>Shopping Cart</h2></div>
  <?php if (!$tot['items']): ?>
    <p class="muted" style="text-align:center;padding:3rem 0">Your cart is empty. <a href="shop.php" style="color:var(--navy2);font-weight:600">Start shopping →</a></p>
  <?php else: ?>
  <div class="cart-page">
    <div>
      <?php foreach ($tot['items'] as $i): ?>
      <div class="cart-item">
        <a href="product.php?slug=<?= e($i['slug']) ?>"><img src="<?= e(productImage($i['image'])) ?>" alt="<?= e($i['name']) ?>"></a>
        <div>
          <b style="color:var(--navy)"><?= e($i['name']) ?></b>
          <div class="muted" style="font-size:.82rem">SKU: <?= e($i['sku']) ?></div>
          <div style="font-weight:700;color:var(--navy2);margin-top:.3rem"><?= money($i['price']) ?></div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.6rem">
          <form method="post" style="display:flex;gap:.4rem;align-items:center">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= $i['id'] ?>">
            <input type="number" name="quantity" value="<?= $i['quantity'] ?>" min="1" max="99" style="width:60px;border:1.5px solid var(--line);border-radius:8px;padding:.4rem">
            <button class="btn btn-ghost btn-sm" name="action" value="update">Update</button>
            <button class="btn btn-ghost btn-sm" name="action" value="remove" style="color:#c0392b">✕</button>
          </form>
          <b><?= money($i['price'] * $i['quantity']) ?></b>
        </div>
      </div>
      <?php endforeach; ?>
      <a href="shop.php" class="btn btn-ghost btn-sm" style="margin-top:1rem">← Continue Shopping</a>
    </div>
    <div class="summary">
      <h4>Order Summary</h4>
        <form class="coupon-row" method="post" action="ajax/coupon.php">
         <?= csrf_field() ?>
        <input type="text" name="code" placeholder="Coupon code">
        <button class="btn btn-dark btn-sm">Apply</button>
      </form>
      <div class="sum-row"><span>Subtotal</span><span><?= money($tot['subtotal']) ?></span></div>
      <div class="sum-row"><span>Shipping</span><span>Calculated at checkout</span></div>
      <div class="sum-row total"><span>Total</span><span><?= money($tot['subtotal']) ?></span></div>
      <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top:1rem">Proceed to Secure Checkout</a>
    </div>
  </div>
  <?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
