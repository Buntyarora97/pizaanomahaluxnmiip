<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
csrf_verify();
// Server-side coupon validation (applied at checkout/order level)
header('Content-Type: application/json');
$code = strtoupper(trim($_POST['code'] ?? ''));
$stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 1 AND (starts_at IS NULL OR starts_at <= CURDATE()) AND (expires_at IS NULL OR expires_at >= CURDATE())");
$stmt->execute([$code]);
$c = $stmt->fetch();
if (!$c) { flash('Invalid or expired coupon code.', 'error'); redirect('../cart.php'); }
$_SESSION['coupon'] = $c['code'];
flash('Coupon "' . $c['code'] . '" will be applied at checkout (min cart: ' . money($c['min_cart']) . ').');
redirect('../cart.php');
