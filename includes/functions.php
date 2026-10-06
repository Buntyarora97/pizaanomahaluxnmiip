<?php
require_once __DIR__ . '/db.php';

function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function money($n) { return MTH_CURRENCY_SYMBOL . number_format((float)$n, 2); }
function setting($key, $default = '') {
    static $cache = null;
    global $pdo;
    if ($cache === null) {
        $cache = [];
        foreach ($pdo->query('SELECT setting_key, setting_value FROM site_settings') as $r) $cache[$r['setting_key']] = $r['setting_value'];
    }
    return $cache[$key] ?? $default;
}
function redirect($path) { header('Location: ' . (strpos($path, 'http') === 0 ? $path : SITE_URL . '/' . ltrim($path, '/'))); exit; }
function flash($msg = null, $type = 'success') {
    if ($msg === null) { $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m; }
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function getCartId() {
    global $pdo;
    if (!isset($_SESSION['cart_id'])) {
        $sid = session_id();
        $stmt = $pdo->prepare('SELECT id FROM carts WHERE session_id = ?');
        $stmt->execute([$sid]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            $pdo->prepare('INSERT INTO carts (session_id) VALUES (?)')->execute([$sid]);
            $id = $pdo->lastInsertId();
        }
        $_SESSION['cart_id'] = $id;
    }
    return $_SESSION['cart_id'];
}
function cartCount() {
    global $pdo;
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE cart_id = ?');
    $stmt->execute([getCartId()]);
    return (int)$stmt->fetchColumn();
}
function cartTotals() {
    global $pdo;
    $stmt = $pdo->prepare('SELECT ci.*, p.name, p.slug, p.sku, p.quote_only,
        (SELECT image FROM product_images WHERE product_id = p.id ORDER BY sort_order LIMIT 1) AS image
        FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ?');
    $stmt->execute([getCartId()]);
    $items = $stmt->fetchAll();
    $subtotal = 0; foreach ($items as $i) $subtotal += $i['price'] * $i['quantity'];
    return ['items' => $items, 'subtotal' => $subtotal, 'count' => count($items)];
}
function orderNumber() {
    global $pdo;
    $year = date('Y');
    $prefix = "MTH-$year-";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_number LIKE ?");
    $stmt->execute(["$prefix%"]);
    return $prefix . str_pad($stmt->fetchColumn() + 1, 6, '0', STR_PAD_LEFT);
}
function sendMail($to, $subject, $html) {
    $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\n";
    $headers .= 'From: ' . (setting('smtp_from_name', 'Mahaluxmi Trading House')) . ' <' . (setting('smtp_from_email', setting('company_email'))) . ">\r\n";
    return @mail($to, $subject, $html, $headers);
}
function productImage($img) {
    if (!$img) return SITE_URL . '/assets/images/placeholder.svg';
    // Allow admin-managed external media URLs while keeping uploaded files
    // relative to the protected uploads directory.
    if (preg_match('#^(?:https?:)?//#i', $img)) return $img;
    if ($img[0] === '/') return SITE_URL . $img;
    return UPLOAD_URL . ltrim($img, '/');
}
