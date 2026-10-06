<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
csrf_verify();
header('Content-Type: application/json');
$d = json_decode(file_get_contents('php://input'), true);
$pid = (int)($d['product_id'] ?? 0);
$vid = !empty($d['variant_id']) ? (int)$d['variant_id'] : null;
$qty = max(1, min(99, (int)($d['quantity'] ?? 1)));
$stmt = $pdo->prepare('SELECT id, price, stock, purchasable, quote_only, status FROM products WHERE id = ?');
$stmt->execute([$pid]);
$p = $stmt->fetch();
if (!$p || !$p['status']) { echo json_encode(['ok'=>0,'msg'=>'Product not found']); exit; }
if (!$p['purchasable'] || $p['quote_only']) { echo json_encode(['ok'=>0,'msg'=>'This product is quote-only. Please request a bulk quote.']); exit; }
$price = $p['price'];
if ($vid) {
    $v = $pdo->prepare('SELECT price, stock, status FROM product_variants WHERE id = ? AND product_id = ?');
    $v->execute([$vid, $pid]);
    if ($vv = $v->fetch()) { if (!$vv['status'] || $vv['stock'] < $qty) { echo json_encode(['ok'=>0,'msg'=>'Variant out of stock']); exit; } $price = $vv['price']; }
}
if ($p['stock'] < $qty) { echo json_encode(['ok'=>0,'msg'=>'Only '.$p['stock'].' in stock']); exit; }
$cartId = getCartId();
$check = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND variant_id <=> ?');
$check->execute([$cartId, $pid, $vid]);
if ($row = $check->fetch()) {
    $newQty = min($row['quantity'] + $qty, $p['stock']);
    $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?')->execute([$newQty, $row['id']]);
} else {
    $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, price) VALUES (?,?,?,?,?)')
        ->execute([$cartId, $pid, $vid, $qty, $price]);
}
echo json_encode(['ok'=>1,'count'=>cartCount()]);
