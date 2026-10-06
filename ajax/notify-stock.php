<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $pid = (int)($_POST['product_id'] ?? 0);
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $pid) {
        $pdo->prepare('INSERT INTO stock_notifications (product_id, email) VALUES (?,?)')->execute([$pid, $email]);
        flash('We will notify you when this product is back in stock.');
    }
}
redirect('../' . (!empty($_SERVER['HTTP_REFERER']) ? basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH)) : 'shop.php'));
