<?php
require_once __DIR__ . '/functions.php';

function customer() { return $_SESSION['customer'] ?? null; }
function requireCustomer() { if (!customer()) { flash('Please login to continue.', 'error'); redirect('login.php'); } }
function attemptLogin($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE email = ? AND status = 1 LIMIT 1');
    $stmt->execute([$email]);
    $c = $stmt->fetch();
    if ($c && password_verify($password, $c['password'])) {
        session_regenerate_id(true);
        unset($c['password']);
        $_SESSION['customer'] = $c;
        return true;
    }
    return false;
}
function isAdmin() { return !empty($_SESSION['admin']); }
function requireAdmin() { if (!isAdmin()) redirect('admin/login.php'); }
function adminCan($perm) {
    if (empty($_SESSION['admin'])) return false;
    if ($_SESSION['admin']['role_id'] == 1) return true;
    return in_array($perm, json_decode($_SESSION['admin']['permissions'] ?? '[]', true) ?: []);
}
