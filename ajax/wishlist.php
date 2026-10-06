<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
csrf_verify();
header('Content-Type: application/json');
$d = json_decode(file_get_contents('php://input'), true);
$pid = (int)($d['product_id'] ?? 0);
if (!customer()) { echo json_encode(['ok'=>0,'msg'=>'Please login to save wishlist']); exit; }
$check = $pdo->prepare('SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?');
$check->execute([customer()['id'], $pid]);
if ($check->fetch()) {
    $pdo->prepare('DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?')->execute([customer()['id'], $pid]);
    echo json_encode(['ok'=>1,'msg'=>'Removed from wishlist']);
} else {
    $pdo->prepare('INSERT IGNORE INTO wishlists (customer_id, product_id) VALUES (?,?)')->execute([customer()['id'], $pid]);
    echo json_encode(['ok'=>1,'msg'=>'Saved to wishlist ♥']);
}
