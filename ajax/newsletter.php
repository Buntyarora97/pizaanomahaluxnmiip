<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
csrf_verify();
header('Content-Type: application/json');
$d = json_decode(file_get_contents('php://input'), true);
$email = trim($d['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['ok'=>0,'msg'=>'Please enter a valid email']); exit; }
try {
    $pdo->prepare('INSERT INTO newsletter_subscribers (email) VALUES (?)')->execute([$email]);
    echo json_encode(['ok'=>1,'msg'=>'Subscribed! Welcome aboard.']);
} catch (PDOException $e) { echo json_encode(['ok'=>0,'msg'=>'You are already subscribed.']); }
