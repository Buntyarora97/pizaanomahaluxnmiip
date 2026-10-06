<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';
if (customer()) redirect('account.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['full_name']); $email = trim($_POST['email']); $phone = trim($_POST['phone']);
    $pass = $_POST['password'];
    if (strlen($pass) < 8) flash('Password must be at least 8 characters.', 'error');
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) flash('Invalid email address.', 'error');
    else {
        try {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO customers (full_name, email, phone, password, company_name, gstin, business_type) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$name, $email, $phone, $hash, trim($_POST['company_name'] ?? '') ?: null, strtoupper(trim($_POST['gstin'] ?? '')) ?: null, trim($_POST['business_type'] ?? '') ?: null]);
            sendMail($email, 'Welcome to Mahaluxmi Trading House', '<h2>Welcome, ' . e($name) . '!</h2><p>Your account is ready. Start exploring products and B2B options.</p>');
            attemptLogin($email, $pass);
            redirect('account.php');
        } catch (PDOException $e) { flash('Email already registered. Please login.', 'error'); }
    }
}
$pageTitle = 'Create Account — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
$fl = flash();
?>
<section class="section"><div class="auth-wrap">
  <h1>Create Account</h1><p class="sub">Shop faster, track orders and unlock B2B features.</p>
  <?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
  <form method="post"><?= csrf_field() ?>
    <div class="field"><label>Full Name *</label><input name="full_name" required></div>
    <div class="form-row">
      <div class="field"><label>Email *</label><input type="email" name="email" required></div>
      <div class="field"><label>Phone *</label><input name="phone" required></div>
    </div>
    <div class="field"><label>Password * (min 8 chars)</label><input type="password" name="password" minlength="8" required></div>
    <p class="muted" style="font-size:.8rem;margin-bottom:1rem;font-weight:600">Optional business details (for B2B):</p>
    <div class="form-row">
      <div class="field"><label>Company Name</label><input name="company_name"></div>
      <div class="field"><label>GSTIN</label><input name="gstin"></div>
    </div>
    <div class="field"><label>Business Type</label><input name="business_type" placeholder="Retailer / Distributor / Institution..."></div>
    <button class="btn btn-primary btn-block">Create Account</button>
  </form>
  <p style="text-align:center;margin-top:1.2rem;font-size:.88rem">Already registered? <a href="login.php" style="color:var(--navy2)">Login</a></p>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
