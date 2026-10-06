<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
csrf_verify();
header('Content-Type: application/json');
$d = json_decode(file_get_contents('php://input'), true);
$id = (int)($d['id'] ?? 0);
if (($d['action'] ?? '') === 'remove') {
    $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND cart_id = ?')->execute([$id, getCartId()]);
} else {
    $qty = max(1, min(99, (int)($d['quantity'] ?? 1)));
    $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?')->execute([$qty, $id, getCartId()]);
}
echo json_encode(['ok'=>1,'count'=>cartCount()]);
