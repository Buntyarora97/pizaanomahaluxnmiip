<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
// Server-side Razorpay order creation. Configure keys in Admin → Settings or config.php
$oid = (int)($_GET['order'] ?? $_SESSION['pending_order'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$oid]);
$o = $stmt->fetch();
if (!$o) redirect('../index.php');

$keyId = setting('razorpay_key_id') ?: RAZORPAY_KEY_ID;
$secret = setting('razorpay_key_secret') ?: RAZORPAY_KEY_SECRET;

if (!$keyId || !$secret) {
    // Gateway not configured — mark as pending manual payment
    flash('Online payment gateway is being configured. Your order is saved — we will contact you for payment.', 'error');
    redirect('../order-success.php?order=' . $o['order_number']);
}

// Create Razorpay order via API
$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => $keyId . ':' . $secret,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['amount' => (int)round($o['grand_total'] * 100), 'currency' => 'INR', 'receipt' => $o['order_number']]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);
$res = json_decode(curl_exec($ch), true);
curl_close($ch);
if (empty($res['id'])) { flash('Could not initiate payment. Please try again.', 'error'); redirect('../checkout.php'); }
$pdo->prepare('UPDATE payments SET gateway_order_id = ? WHERE order_id = ?')->execute([$res['id'], $oid]);
$prefill = ['name' => '', 'email' => '', 'contact' => $o['guest_phone'] ?? ''];
if ($o['customer_id']) {
    $c = $pdo->prepare('SELECT * FROM customers WHERE id = ?'); $c->execute([$o['customer_id']]); $c = $c->fetch();
    if ($c) $prefill = ['name' => $c['full_name'], 'email' => $c['email'], 'contact' => $c['phone']];
}
$pageTitle = 'Complete Payment — ' . setting('site_name');
include __DIR__ . '/../includes/header.php';
?>
<section class="section"><div class="container" style="max-width:520px;text-align:center">
  <div class="acct-card">
    <h3>Complete Your Payment</h3>
    <p class="muted">Order <b><?= e($o['order_number']) ?></b> · <b style="color:var(--navy2)"><?= money($o['grand_total']) ?></b></p>
    <button id="rzpBtn" class="btn btn-primary btn-block" style="margin-top:1.4rem">PAY <?= money($o['grand_total']) ?> SECURELY</button>
    <p class="muted" style="font-size:.78rem;margin-top:1rem">Secured by Razorpay · UPI, Cards, NetBanking, Wallets</p>
  </div>
</div></section>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('rzpBtn').onclick = function () {
  const rzp = new Razorpay({
    key: '<?= e($keyId) ?>',
    order_id: '<?= e($res['id']) ?>',
    amount: <?= (int)round($o['grand_total'] * 100) ?>,
    currency: 'INR',
    name: 'Mahaluxmi Trading House Pvt. Ltd.',
    description: 'Order <?= e($o['order_number']) ?>',
    prefill: <?= json_encode($prefill) ?>,
    theme: { color: '#0D3B66' },
    handler: function (resp) {
      fetch('<?= SITE_URL ?>/payment/verify.php', {
        method: 'POST', headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ razorpay_order_id: resp.razorpay_order_id, razorpay_payment_id: resp.razorpay_payment_id, razorpay_signature: resp.razorpay_signature, order_id: <?= $oid ?> })
      }).then(r => r.json()).then(d => {
        if (d.ok) location.href = '<?= SITE_URL ?>/order-success.php?order=<?= e($o['order_number']) ?>';
        else alert('Payment verification failed. Contact support with order <?= e($o['order_number']) ?>.');
      });
    }
  });
  rzp.open();
};
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
