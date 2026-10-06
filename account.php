<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth.php';
requireCustomer();
$c = customer();
$sec = $_GET['s'] ?? 'overview';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (($_POST['form'] ?? '') === 'profile') {
        $pdo->prepare('UPDATE customers SET full_name=?, phone=?, company_name=?, gstin=?, business_type=? WHERE id=?')
            ->execute([trim($_POST['full_name']), trim($_POST['phone']), trim($_POST['company_name'] ?? '') ?: null, trim($_POST['gstin'] ?? '') ?: null, trim($_POST['business_type'] ?? '') ?: null, $c['id']]);
        flash('Profile updated.');
    } elseif (($_POST['form'] ?? '') === 'password') {
        $cur = $pdo->prepare('SELECT password FROM customers WHERE id=?'); $cur->execute([$c['id']]);
        if (password_verify($_POST['current'], $cur->fetchColumn())) {
            $pdo->prepare('UPDATE customers SET password=? WHERE id=?')->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $c['id']]);
            flash('Password changed.');
        } else flash('Current password incorrect.', 'error');
    } elseif (($_POST['form'] ?? '') === 'address') {
        $pdo->prepare('INSERT INTO customer_addresses (customer_id, type, name, phone, address, landmark, city, state, pin_code) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$c['id'], $_POST['type'], trim($_POST['name']), trim($_POST['phone']), trim($_POST['address']), trim($_POST['landmark'] ?? '') ?: null, trim($_POST['city']), trim($_POST['state']), trim($_POST['pin'])]);
        flash('Address added.');
    } elseif (($_POST['form'] ?? '') === 'b2b') {
        $pdo->prepare("UPDATE customers SET is_business=1, b2b_status='pending', company_name=?, gstin=?, business_type=? WHERE id=?")
            ->execute([trim($_POST['company']), trim($_POST['gstin']), trim($_POST['business_type']), $c['id']]);
        flash('B2B application submitted. Our team will review it.');
    }
    // refresh session customer
    $stmt = $pdo->prepare('SELECT id, full_name, email, phone, company_name, gstin, business_type, is_business, b2b_status FROM customers WHERE id=?');
    $stmt->execute([$c['id']]); $_SESSION['customer'] = $stmt->fetch(); $c = $_SESSION['customer'];
    redirect('account.php?s=' . $sec);
}

$orders = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC');
$orders->execute([$c['id']]); $orders = $orders->fetchAll();
$addresses = $pdo->prepare('SELECT * FROM customer_addresses WHERE customer_id = ?');
$addresses->execute([$c['id']]); $addresses = $addresses->fetchAll();
$wish = $pdo->prepare('SELECT w.id AS wid, p.*, (SELECT image FROM product_images WHERE product_id=p.id LIMIT 1) AS img FROM wishlists w JOIN products p ON p.id=w.product_id WHERE w.customer_id=?');
$wish->execute([$c['id']]); $wishlist = $wish->fetchAll();
$pageTitle = 'My Account — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
$fl = flash();
$nav = ['overview'=>'Overview','orders'=>'Orders','addresses'=>'Addresses','wishlist'=>'Wishlist','profile'=>'Profile','business'=>'Business Profile'];
?>
<section class="section"><div class="container">
  <div class="acct-layout">
    <nav class="acct-nav">
      <div style="padding:.6rem 1rem;font-weight:700;color:var(--navy)">👤 <?= e(explode(' ', $c['full_name'])[0]) ?></div>
      <?php foreach ($nav as $k => $v): ?><a href="?s=<?= $k ?>" class="<?= $sec===$k?'active':'' ?>"><?= $v ?></a><?php endforeach; ?>
      <a href="logout.php" style="color:#c0392b">Logout</a>
    </nav>
    <div>
      <?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>

      <?php if ($sec === 'overview'): ?>
      <div class="acct-card">
        <h3>Dashboard</h3>
        <div class="stat-grid">
          <div class="stat"><b><?= count($orders) ?></b><span>Total Orders</span></div>
          <div class="stat"><b><?= count($wishlist) ?></b><span>Wishlist Items</span></div>
          <div class="stat"><b><?= count($addresses) ?></b><span>Saved Addresses</span></div>
          <div class="stat"><b style="font-size:1rem;text-transform:uppercase"><?= e($c['b2b_status']) ?></b><span>B2B Status</span></div>
        </div>
      </div>
      <div class="acct-card"><h3>Recent Orders</h3>
        <?php if (!$orders): ?><p class="muted">No orders yet. <a href="shop.php" style="color:var(--navy2)">Shop now →</a></p>
        <?php else: ?><table class="tbl"><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr>
        <?php foreach (array_slice($orders,0,5) as $o): ?><tr><td><?= e($o['order_number']) ?></td><td><?= e(date('d M Y', strtotime($o['placed_at']))) ?></td><td><?= money($o['grand_total']) ?></td><td><span class="badge <?= e($o['status']) ?>"><?= e(ucwords(str_replace('_',' ',$o['status']))) ?></span></td><td><a href="track-order.php?order=<?= e($o['order_number']) ?>" style="color:var(--navy2);font-weight:600">Track</a></td></tr><?php endforeach; ?></table><?php endif; ?>
      </div>

      <?php elseif ($sec === 'orders'): ?>
      <div class="acct-card"><h3>All Orders</h3>
        <?php if (!$orders): ?><p class="muted">No orders yet.</p>
        <?php else: ?><table class="tbl"><tr><th>Order</th><th>Date</th><th>Items Total</th><th>Payment</th><th>Status</th><th></th></tr>
        <?php foreach ($orders as $o): ?><tr><td><?= e($o['order_number']) ?></td><td><?= e(date('d M Y', strtotime($o['placed_at']))) ?></td><td><?= money($o['grand_total']) ?></td><td><span class="badge <?= e($o['payment_status']) ?>"><?= e($o['payment_status']) ?></span></td><td><span class="badge <?= e($o['status']) ?>"><?= e(ucwords(str_replace('_',' ',$o['status']))) ?></span></td><td><a href="invoice.php?order=<?= e($o['order_number']) ?>" style="color:var(--navy2);font-weight:600">Invoice</a></td></tr><?php endforeach; ?></table><?php endif; ?>
      </div>

      <?php elseif ($sec === 'addresses'): ?>
      <div class="acct-card"><h3>Saved Addresses</h3>
        <?php foreach ($addresses as $a): ?><div style="border:1px solid var(--line);border-radius:12px;padding:1rem;margin-bottom:.8rem"><b><?= e($a['type'] === 'billing' ? 'Billing' : 'Shipping') ?></b> — <?= e($a['name']) ?>, <?= e($a['address']) ?>, <?= e($a['city']) ?>, <?= e($a['state']) ?> - <?= e($a['pin_code']) ?>, <?= e($a['phone']) ?></div><?php endforeach; ?>
        <h3 style="margin-top:1.5rem">Add New Address</h3>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="form" value="address">
          <div class="form-row"><div class="field"><label>Type</label><select name="type"><option value="shipping">Shipping</option><option value="billing">Billing</option></select></div>
          <div class="field"><label>Name</label><input name="name" required></div></div>
          <div class="form-row"><div class="field"><label>Phone</label><input name="phone" required></div><div class="field"><label>Landmark</label><input name="landmark"></div></div>
          <div class="field"><label>Address</label><textarea name="address" required></textarea></div>
          <div class="form-row"><div class="field"><label>City</label><input name="city" required></div><div class="field"><label>State</label><input name="state" required></div></div>
          <div class="field"><label>PIN Code</label><input name="pin" required pattern="\d{6}"></div>
          <button class="btn btn-primary">Save Address</button>
        </form>
      </div>

      <?php elseif ($sec === 'wishlist'): ?>
      <div class="acct-card"><h3>My Wishlist</h3>
        <?php if (!$wishlist): ?><p class="muted">Your wishlist is empty.</p>
        <?php else: ?><div class="p-grid" style="grid-template-columns:repeat(3,1fr)">
          <?php foreach ($wishlist as $w): ?><div class="p-card"><div class="p-img"><a href="product.php?slug=<?= e($w['slug']) ?>"><img src="<?= e(productImage($w['img'])) ?>" alt="<?= e($w['name']) ?>"></a></div><div class="p-body"><span class="p-name"><?= e($w['name']) ?></span><div class="p-price"><span class="now"><?= money($w['price']) ?></span></div></div></div><?php endforeach; ?>
        </div><?php endif; ?>
      </div>

      <?php elseif ($sec === 'profile'): ?>
      <div class="acct-card"><h3>Edit Profile</h3>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="form" value="profile">
          <div class="field"><label>Full Name</label><input name="full_name" value="<?= e($c['full_name']) ?>"></div>
          <div class="form-row"><div class="field"><label>Phone</label><input name="phone" value="<?= e($c['phone']) ?>"></div><div class="field"><label>Email (locked)</label><input value="<?= e($c['email']) ?>" disabled></div></div>
          <div class="form-row"><div class="field"><label>Company</label><input name="company_name" value="<?= e($c['company_name'] ?? '') ?>"></div><div class="field"><label>GSTIN</label><input name="gstin" value="<?= e($c['gstin'] ?? '') ?>"></div></div>
          <div class="field"><label>Business Type</label><input name="business_type" value="<?= e($c['business_type'] ?? '') ?>"></div>
          <button class="btn btn-primary">Save Changes</button>
        </form>
        <h3 style="margin-top:2rem">Change Password</h3>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="form" value="password">
          <div class="form-row"><div class="field"><label>Current Password</label><input type="password" name="current" required></div><div class="field"><label>New Password</label><input type="password" name="new_password" minlength="8" required></div></div>
          <button class="btn btn-dark">Update Password</button>
        </form>
      </div>

      <?php elseif ($sec === 'business'): ?>
      <div class="acct-card"><h3>Business Profile (B2B)</h3>
        <?php if ($c['b2b_status'] === 'approved'): ?><div class="alert success">🎉 Your business account is <b>approved</b>. Wholesale pricing is now visible where configured.</div>
        <?php elseif ($c['b2b_status'] === 'pending'): ?><div class="alert">Your B2B application is <b>under review</b>. We will contact you shortly.</div>
        <?php else: ?>
        <p class="muted" style="margin-bottom:1.2rem">Apply for a business account to access wholesale pricing, bulk ordering and dedicated support.</p>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="form" value="b2b">
          <div class="form-row"><div class="field"><label>Company Name *</label><input name="company" required></div><div class="field"><label>GSTIN</label><input name="gstin"></div></div>
          <div class="field"><label>Business Type *</label><input name="business_type" placeholder="Distributor / Retailer / Institution / Exporter..." required></div>
          <button class="btn btn-gold">Submit B2B Application</button>
        </form><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
