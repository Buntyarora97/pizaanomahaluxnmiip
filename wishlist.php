<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Wishlist — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container">
  <div class="sec-head"><span class="sec-eyebrow">Saved Items</span><h2>My Wishlist</h2></div>
  <?php if (customer()):
      $wish = $pdo->prepare('SELECT p.*, (SELECT image FROM product_images WHERE product_id=p.id LIMIT 1) AS img FROM wishlists w JOIN products p ON p.id=w.product_id WHERE w.customer_id=?');
      $wish->execute([customer()['id']]); $items = $wish->fetchAll(); ?>
    <?php if (!$items): ?><p class="muted" style="text-align:center">Wishlist empty. Tap the heart on any product.</p>
    <?php else: ?><div class="p-grid"><?php foreach ($items as $p): ?><div class="p-card"><div class="p-img"><a href="product.php?slug=<?= e($p['slug']) ?>"><img src="<?= e(productImage($p['img'])) ?>" alt="<?= e($p['name']) ?>"></a></div><div class="p-body"><span class="p-name"><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></span><div class="p-price"><span class="now"><?= money($p['price']) ?></span></div></div></div><?php endforeach; ?></div><?php endif; ?>
  <?php else: ?>
    <div class="auth-wrap"><h1>Login Required</h1><p class="sub">Login to view your saved wishlist. Guest wishlist is stored on this device.</p><div id="guestWish"></div><a href="login.php" class="btn btn-primary btn-block">Login</a></div>
    <script>const gw=JSON.parse(localStorage.getItem('mth_wish')||'[]');document.getElementById('guestWish').innerHTML=gw.length?'<p class="muted" style="margin-bottom:1rem">'+gw.length+' item(s) saved on this device.</p>':'<p class="muted" style="margin-bottom:1rem">No saved items on this device.</p>';</script>
  <?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
