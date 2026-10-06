<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';
$tot = cartTotals();
if (!$tot['items']) { flash('Your cart is empty.', 'error'); redirect('shop.php'); }
$customer = customer();
$guestOk = setting('guest_checkout', '1') === '1';
if (!$customer && !$guestOk) { flash('Please login to checkout.', 'error'); redirect('login.php'); }

$shipMethods = $pdo->query("SELECT m.*, z.name AS zone FROM shipping_methods m JOIN shipping_zones z ON z.id=m.zone_id WHERE m.status=1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? ''); $phone = trim($_POST['phone'] ?? ''); $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? ''); $city = trim($_POST['city'] ?? ''); $state = trim($_POST['state'] ?? ''); $pin = trim($_POST['pin'] ?? '');
        $payMethod = $_POST['payment_method'] ?? 'cod';
        if (!in_array($payMethod, ['cod', 'razorpay', 'bank'], true)) $payMethod = 'cod';
    if (!$name || !$phone || !$address || !$city || !$state || !$pin || (!$customer && !$email)) {
        flash('Please fill all required fields.', 'error');
    } else {
        // Server-side recalculation — never trust client totals
        $subtotal = 0; $itemsCalc = [];
            foreach ($tot['items'] as $i) {
             $check = $pdo->prepare('SELECT price, stock, name, sku, purchasable, quote_only FROM products WHERE id = ? AND status = 1 FOR UPDATE');
            $check->execute([$i['product_id']]);
            $prod = $check->fetch();
             if (!$prod || !$prod['purchasable'] || $prod['quote_only'] || $prod['stock'] < $i['quantity']) { flash('Some items are out of stock or require a quote. Please review your cart.', 'error'); redirect('cart.php'); }
            $subtotal += $prod['price'] * $i['quantity'];
            $itemsCalc[] = ['product_id'=>$i['product_id'],'name'=>$prod['name'],'sku'=>$prod['sku'],'qty'=>$i['quantity'],'price'=>$prod['price']];
        }
        $shipId = (int)($_POST['shipping_method'] ?? 0);
        $ship = 0;
        foreach ($shipMethods as $m) {
            if ((int)$m['id'] === $shipId) {
                $ship = $m['type'] === 'free' ? 0 : (float)$m['rate'];
                break;
            }
        }
        $grand = $subtotal + $ship;
        $addr = "$name, $phone, $address, $city, $state - $pin, India";
        $onum = orderNumber();
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO orders (order_number, customer_id, guest_email, guest_phone, billing_address, shipping_address, subtotal, shipping_total, grand_total, payment_method)
                VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$onum, $customer['id'] ?? null, $email ?: null, $phone, $addr, $addr, $subtotal, $ship, $grand, $payMethod]);
            $oid = $pdo->lastInsertId();
            $oi = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, sku, quantity, price) VALUES (?,?,?,?,?,?)');
            $mv = $pdo->prepare('INSERT INTO inventory_movements (product_id, stock_change, reason, reference) VALUES (?,?,?,?)');
            $upd = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            foreach ($itemsCalc as $it) {
                $oi->execute([$oid, $it['product_id'], $it['name'], $it['sku'], $it['qty'], $it['price']]);
                $upd->execute([$it['qty'], $it['product_id'], $it['qty']]);
                if ($upd->rowCount() !== 1) throw new RuntimeException('Stock changed while placing order.');
                $mv->execute([$it['product_id'], -$it['qty'], 'sale', $onum]);
            }
            $pdo->prepare('INSERT INTO order_status_history (order_id, status) VALUES (?,?)')->execute([$oid, 'placed']);
            $pdo->prepare('INSERT INTO payments (order_id, gateway, amount, status) VALUES (?,?,?,?)')->execute([$oid, $payMethod === 'cod' ? 'cod' : 'razorpay', $grand, $payMethod === 'cod' ? 'pending' : 'pending']);
            $pdo->prepare('INSERT INTO invoices (order_id, invoice_number) VALUES (?,?)')->execute([$oid, 'INV-' . $onum]);
            $pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([getCartId()]);
            $pdo->commit();
            // Emails
            $to = $customer['email'] ?? $email;
            if ($to) sendMail($to, "Order Received — $onum", "<h2>Thank you for your order!</h2><p>Order <b>$onum</b> · Total <b>" . money($grand) . "</b></p><p>We will update you as it progresses.</p>");
            sendMail(setting('company_email'), "New Order $onum", "<p>New order placed: $onum · " . money($grand) . "</p>");
            if ($payMethod === 'razorpay') { $_SESSION['pending_order'] = $oid; redirect('payment/create-order.php?order=' . $oid); }
            redirect('order-success.php?order=' . $onum);
        } catch (Exception $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            flash('Could not place order. Please try again.', 'error');
        }
    }
}
$pageTitle = 'Secure Checkout — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container">
  <div class="sec-head"><span class="sec-eyebrow">Almost There</span><h2>Secure Checkout</h2></div>
  <div class="steps"><span class="on">1. Address</span><span class="on">2. Shipping</span><span class="on">3. Payment</span><span>4. Confirmation</span></div>
  <form class="cart-page" method="post">
    <?= csrf_field() ?>
    <div class="acct-card">
      <h3>Delivery Details</h3>
      <div class="form-row">
        <div class="field"><label>Full Name *</label><input name="name" required value="<?= e($customer['full_name'] ?? '') ?>"></div>
        <div class="field"><label>Phone *</label><input name="phone" required value="<?= e($customer['phone'] ?? '') ?>"></div>
      </div>
      <?php if (!$customer): ?><div class="field"><label>Email *</label><input type="email" name="email" required></div><?php endif; ?>
      <div class="field"><label>Address *</label><textarea name="address" required></textarea></div>
      <div class="form-row">
        <div class="field"><label>City *</label><input name="city" required></div>
        <div class="field"><label>State *</label><input name="state" required></div>
      </div>
      <div class="form-row">
        <div class="field"><label>PIN Code *</label><input name="pin" required pattern="\d{6}"></div>
        <div class="field"><label>Shipping Method</label>
          <select name="shipping_method">
            <?php foreach ($shipMethods as $m): ?><option value="<?= $m['id'] ?>"><?= e($m['name']) ?> (<?= $m['type']==='free' ? 'FREE' : money($m['rate']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <h3 style="margin-top:1.5rem">Payment Method</h3>
      <label class="pay-opt"><input type="radio" name="payment_method" value="cod" checked> <b>Cash on Delivery</b></label>
      <label class="pay-opt"><input type="radio" name="payment_method" value="razorpay"> <b>Online Payment</b> <span class="muted">(UPI / Cards / NetBanking via Razorpay)</span></label>
      <label class="pay-opt"><input type="radio" name="payment_method" value="bank"> <b>Bank Transfer</b> <span class="muted">(Details shared after order)</span></label>
    </div>
    <div class="summary">
      <h4>Order Summary</h4>
      <?php foreach ($tot['items'] as $i): ?>
      <div class="sum-row"><span><?= e($i['name']) ?> × <?= $i['quantity'] ?></span><span><?= money($i['price']*$i['quantity']) ?></span></div>
      <?php endforeach; ?>
      <div class="sum-row total"><span>Subtotal</span><span><?= money($tot['subtotal']) ?></span></div>
      <p class="tax-note" style="margin:.8rem 0">Taxes (GST) &amp; shipping are confirmed before final payment. Totals are recalculated securely on our server.</p>
      <button class="btn btn-primary btn-block" style="margin-top:.6rem">PLACE ORDER</button>
    </div>
  </form>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
