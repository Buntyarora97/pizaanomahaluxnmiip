<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare('SELECT id FROM customers WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $token = bin2hex(random_bytes(32));
        $pdo->prepare('INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 1 HOUR))')->execute([$email, $token]);
        sendMail($email, 'Password Reset', '<p>Reset link (valid 1 hour): <a href="' . SITE_URL . '/forgot-password.php?token=' . $token . '">' . SITE_URL . '/forgot-password.php?token=' . $token . '</a></p>');
    }
    flash('If this email is registered, a reset link has been sent.');
}
if (!empty($_GET['token']) && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_password'])) {
    $t = $pdo->prepare('SELECT email FROM password_reset_tokens WHERE token = ? AND used = 0 AND expires_at > NOW()');
    $t->execute([$_GET['token']]);
    if ($row = $t->fetch()) {
        $pdo->prepare('UPDATE customers SET password = ? WHERE email = ?')->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $row['email']]);
        $pdo->prepare('UPDATE password_reset_tokens SET used = 1 WHERE token = ?')->execute([$_GET['token']]);
        flash('Password updated. Please login.'); redirect('login.php');
    }
}
$pageTitle = 'Reset Password — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
$fl = flash();
?>
<section class="section"><div class="auth-wrap">
  <h1>Reset Password</h1>
  <?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
  <?php if (!empty($_GET['token'])): ?>
  <form method="post"><?= csrf_field() ?><div class="field"><label>New Password</label><input type="password" name="new_password" minlength="8" required></div><button class="btn btn-primary btn-block">Update Password</button></form>
  <?php else: ?>
  <form method="post"><?= csrf_field() ?><div class="field"><label>Registered Email</label><input type="email" name="email" required></div><button class="btn btn-primary btn-block">Send Reset Link</button></form>
  <?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
