<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';
if (customer()) redirect('account.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (attemptLogin(trim($_POST['email']), $_POST['password'])) redirect('account.php');
    flash('Invalid email or password.', 'error');
}
$pageTitle = 'Login — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
$fl = flash();
?>
<section class="section"><div class="auth-wrap">
  <h1>Welcome Back</h1><p class="sub">Login to your Mahaluxmi Trading account.</p>
  <?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <div class="field"><label>Email</label><input type="email" name="email" required></div>
    <div class="field"><label>Password</label><input type="password" name="password" required></div>
    <button class="btn btn-primary btn-block">Login</button>
  </form>
  <p style="text-align:center;margin-top:1.2rem;font-size:.88rem"><a href="forgot-password.php" style="color:var(--navy2)">Forgot password?</a> · <a href="register.php" style="color:var(--navy2)">Create account</a></p>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
