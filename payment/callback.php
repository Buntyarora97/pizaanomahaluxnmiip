<?php
// Razorpay webhook endpoint — set this URL in Razorpay Dashboard → Webhooks
require_once __DIR__ . '/../includes/functions.php';
$body = file_get_contents('php://input');
$sig = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
$secret = setting('razorpay_key_secret') ?: RAZORPAY_KEY_SECRET;
if (!hash_equals(hash_hmac('sha256', $body, $secret), $sig)) { http_response_code(401); exit('Invalid signature'); }
$event = json_decode($body, true);
if (($event['event'] ?? '') === 'payment.captured') {
    $pay = $event['payload']['payment']['entity'] ?? [];
    $orderId = $pay['order_id'] ?? '';
    $stmt = $pdo->prepare('SELECT order_id, status FROM payments WHERE gateway_order_id = ?');
    $stmt->execute([$orderId]);
    if ($row = $stmt->fetch()) {
        if ($row['status'] !== 'paid') {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE payments SET status='paid', gateway_txn_id=? WHERE gateway_order_id=? AND status!='paid'")->execute([$pay['id'] ?? '', $orderId]);
            $pdo->prepare("UPDATE orders SET payment_status='paid', status='confirmed' WHERE id=? AND payment_status!='paid'")->execute([$row['order_id']]);
            $pdo->prepare("INSERT INTO order_status_history (order_id, status) VALUES (?, 'confirmed')")->execute([$row['order_id']]);
            $pdo->commit();
        }
    }
}
http_response_code(200); echo 'OK';
