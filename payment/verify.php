<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
$d = json_decode(file_get_contents('php://input'), true);
$oid = (int)($d['order_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$oid]);
$o = $stmt->fetch();
if (!$o) { echo json_encode(['ok'=>0]); exit; }
// Idempotency: already paid?
if ($o['payment_status'] === 'paid') { echo json_encode(['ok'=>1]); exit; }
$keyId = setting('razorpay_key_id') ?: RAZORPAY_KEY_ID;
$secret = setting('razorpay_key_secret') ?: RAZORPAY_KEY_SECRET;
$expected = hash_hmac('sha256', $d['razorpay_order_id'] . '|' . $d['razorpay_payment_id'], $secret);
if (!hash_equals($expected, $d['razorpay_signature'] ?? '')) { echo json_encode(['ok'=>0]); exit; }
$pdo->beginTransaction();
$pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'confirmed' WHERE id = ? AND payment_status != 'paid'")->execute([$oid]);
$pdo->prepare("UPDATE payments SET status = 'paid', gateway_txn_id = ?, method = 'razorpay' WHERE order_id = ?")->execute([$d['razorpay_payment_id'], $oid]);
$pdo->prepare("INSERT INTO order_status_history (order_id, status) VALUES (?, 'confirmed')")->execute([$oid]);
$pdo->commit();
echo json_encode(['ok'=>1]);
