<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
$onum = $_GET['order'] ?? '';
$stmt = $pdo->prepare('SELECT o.*, inv.invoice_number, inv.issued_at FROM orders o LEFT JOIN invoices inv ON inv.order_id=o.id WHERE o.order_number = ?');
$stmt->execute([$onum]);
$o = $stmt->fetch();
if (!$o || ($o['customer_id'] && customer() && $o['customer_id'] != customer()['id'])) redirect('index.php');
$items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?'); $items->execute([$o['id']]); $items = $items->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invoice <?= e($o['invoice_number'] ?: $o['order_number']) ?></title>
<style>body{font-family:Arial,sans-serif;color:#1B2A3B;max-width:800px;margin:30px auto;padding:0 20px}h1{color:#0D3B66}.box{border:1px solid #E3EAF2;border-radius:8px;padding:18px;margin:14px 0}table{width:100%;border-collapse:collapse;font-size:14px}th,td{padding:9px;border-bottom:1px solid #E3EAF2;text-align:left}th{background:#F7F9FC}.tot{text-align:right;font-size:16px;font-weight:bold}.btn{display:inline-block;background:#0D3B66;color:#fff;padding:10px 22px;border-radius:99px;text-decoration:none;margin-top:16px}</style></head><body>
<div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:16px">
  <div><h1>MAHALUXMI TRADING HOUSE PVT. LTD.</h1>
    <p style="font-size:13px;color:#5B6B80"><?= nl2br(e(setting('registered_address'))) ?><br>
    CIN: <?= e(setting('cin')) ?><?= setting('gstin') ? ' · GSTIN: ' . e(setting('gstin')) : '' ?><br>
    <?= e(setting('company_email')) ?> · <?= e(setting('company_mobile')) ?></p></div>
  <div style="text-align:right"><h2>TAX INVOICE</h2><p style="font-size:13px">Invoice: <?= e($o['invoice_number'] ?: 'INV-' . $o['order_number']) ?><br>Order: <?= e($o['order_number']) ?><br>Date: <?= e(date('d M Y', strtotime($o['issued_at'] ?? $o['placed_at']))) ?></p></div>
</div>
<div class="box"><b>Bill To / Ship To:</b><br><?= nl2br(e($o['shipping_address'])) ?></div>
<table><tr><th>Item</th><th>SKU</th><th>HSN</th><th>Qty</th><th>Rate</th><th>Amount</th></tr>
<?php foreach ($items as $i): ?><tr><td><?= e($i['product_name']) ?><?= $i['variant_name'] ? ' (' . e($i['variant_name']) . ')' : '' ?></td><td><?= e($i['sku']) ?></td><td><?= e($i['hsn']) ?></td><td><?= $i['quantity'] ?></td><td><?= money($i['price']) ?></td><td><?= money($i['price']*$i['quantity']) ?></td></tr><?php endforeach; ?>
</table>
<p class="tot">Subtotal: <?= money($o['subtotal']) ?><br>Shipping: <?= money($o['shipping_total']) ?><?= $o['discount'] ? '<br>Discount: -' . money($o['discount']) : '' ?><br>Grand Total: <?= money($o['grand_total']) ?></p>
<p style="font-size:12px;color:#5B6B80">Payment Method: <?= e(strtoupper($o['payment_method'])) ?> · Payment Status: <?= e(ucfirst($o['payment_status'])) ?></p>
<button class="btn" onclick="window.print()">Print Invoice</button>
</body></html>
