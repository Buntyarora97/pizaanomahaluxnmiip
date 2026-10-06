<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
$pre = null;
if (!empty($_GET['product'])) {
    $s = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $s->execute([(int)$_GET['product']]); $pre = $s->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $qn = 'BQ-' . date('Y') . '-' . str_pad($pdo->query('SELECT COUNT(*) FROM bulk_quotes')->fetchColumn() + 1, 5, '0', STR_PAD_LEFT);
    $pdo->prepare('INSERT INTO bulk_quotes (quote_number, company, contact_person, phone, email, gstin, business_type, destination, requirement_date, message) VALUES (?,?,?,?,?,?,?,?,?,?)')
        ->execute([$qn, trim($_POST['company']), trim($_POST['contact']), trim($_POST['phone']), trim($_POST['email']), trim($_POST['gstin'] ?? '') ?: null, trim($_POST['business_type'] ?? '') ?: null, trim($_POST['destination'] ?? '') ?: null, $_POST['req_date'] ?: null, trim($_POST['message'] ?? '') ?: null]);
    if (!empty($_POST['product_id'])) {
        $pdo->prepare('INSERT INTO bulk_quote_items (quote_id, product_id, quantity) VALUES (?,?,?)')
            ->execute([$pdo->lastInsertId(), (int)$_POST['product_id'], max(1, (int)$_POST['quantity'])]);
    }
    sendMail(setting('company_email'), "New Bulk Quote $qn", '<p>Bulk quote request received.</p><p>Company: ' . e($_POST['company']) . '<br>Contact: ' . e($_POST['contact']) . '<br>Qty: ' . e($_POST['quantity'] ?? '-') . '</p>');
    sendMail(trim($_POST['email']), "Bulk Quote Request Received — $qn", '<p>Thank you! Our trade team will contact you shortly with pricing.</p>');
    flash('Bulk quote request submitted! Reference: ' . $qn);
}
$pageTitle = 'Request Bulk Quote — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
$fl = flash();
?>
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="index.php">Home</a> / Bulk Quote</div><h1>Request Bulk Quote</h1><p>Tell us your requirement — our trade team responds with wholesale pricing.</p></div></section>
<section class="section"><div class="container" style="max-width:640px">
  <div class="form-card" style="max-width:none">
    <?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
    <?php if ($pre): ?><div class="alert success">Product: <b><?= e($pre['name']) ?></b> (SKU: <?= e($pre['sku']) ?>)</div><?php endif; ?>
    <form method="post"><?= csrf_field() ?>
      <?php if ($pre): ?><input type="hidden" name="product_id" value="<?= $pre['id'] ?>"><?php endif; ?>
      <div class="form-row"><div class="field"><label>Company *</label><input name="company" required></div><div class="field"><label>Contact Person *</label><input name="contact" required></div></div>
      <div class="form-row"><div class="field"><label>Phone *</label><input name="phone" required></div><div class="field"><label>Email *</label><input type="email" name="email" required></div></div>
      <div class="form-row"><div class="field"><label>GSTIN</label><input name="gstin"></div><div class="field"><label>Business Type</label><input name="business_type" placeholder="Distributor / Retailer / Institution..."></div></div>
      <?php if ($pre): ?><div class="form-row"><div class="field"><label>Quantity *</label><input type="number" name="quantity" min="1" value="100" required></div><div class="field"><label>Requirement Date</label><input type="date" name="req_date"></div></div>
      <?php else: ?><div class="field"><label>Requirement Date</label><input type="date" name="req_date"></div><?php endif; ?>
      <div class="field"><label>Destination / Delivery City</label><input name="destination"></div>
      <div class="field"><label>Requirement Details</label><textarea name="message" placeholder="Products, specifications, quantities..."></textarea></div>
      <button class="btn btn-gold btn-block">SUBMIT QUOTE REQUEST</button>
    </form>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
