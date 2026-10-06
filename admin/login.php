<?php
require_once __DIR__ . '/../includes/functions.php';
if (isAdmin()) redirect('dashboard.php');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT a.*, r.permissions FROM admins a LEFT JOIN admin_roles r ON r.id=a.role_id WHERE a.email = ? AND a.status = 1');
    $stmt->execute([trim($_POST['email'])]);
    $a = $stmt->fetch();
    if ($a && password_verify($_POST['password'], $a['password'])) {
        session_regenerate_id(true);
        unset($a['password']);
        $_SESSION['admin'] = $a;
        $pdo->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?')->execute([$a['id']]);
        redirect('dashboard.php');
    }
    $err = 'Invalid credentials.';
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="robots" content="noindex,nofollow">
<title>Admin Login — Mahaluxmi Trading House</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css"></head>
<body class="admin-login"><div class="auth-wrap">
  <div style="text-align:center;margin-bottom:1.6rem"><span class="logo-mark" style="width:52px;height:52px;display:inline-grid;place-items:center;border-radius:14px;background:linear-gradient(135deg,var(--navy2),var(--cyan));color:#fff;font-size:1.4rem">◆</span>
  <h1 style="margin-top:.8rem">Admin Panel</h1><p class="sub">Mahaluxmi Trading House Pvt. Ltd.</p></div>
  <?php if ($err): ?><div class="alert error"><?= e($err) ?></div><?php endif; ?>
  <form method="post">
    <div class="field"><label>Email</label><input type="email" name="email" required></div>
    <div class="field"><label>Password</label><input type="password" name="password" required></div>
    <button class="btn btn-primary btn-block">Login to Dashboard</button>
  </form>
</div></body></html>
