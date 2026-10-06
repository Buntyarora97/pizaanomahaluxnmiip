<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('INSERT INTO brands (name, slug) VALUES (?,?)')
        ->execute([trim($_POST['name']), strtolower(preg_replace('/[^a-z0-9]+/i','-',$_POST['name'])) . '-' . substr(bin2hex(random_bytes(2)),0,4)]);
    flash('Saved successfully.');
    redirect(basename($_SERVER['PHP_SELF']));
}
if (isset($_GET['toggle'])) {
    $pdo->prepare('UPDATE brands SET status = 1 - status WHERE id = ?')->execute([(int)$_GET['toggle']]);
    redirect(basename($_SERVER['PHP_SELF']));
}
if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM brands WHERE id = ?')->execute([(int)$_GET['delete']]);
    flash('Deleted.'); redirect(basename($_SERVER['PHP_SELF']));
}
$rows = $pdo->query( + table_sql + r)->fetchAll();
?>
<div class="admin-top"><h1>Brands</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Add New</h3>
<form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.8rem;align-items:end"><?= csrf_field() ?>
<div class="field"><label>Brand Name *</label><input name="name" required></div>
<button class="btn btn-primary">Save</button></form>
<p class="muted" style="font-size:.78rem;margin-top:.6rem"></p></div>
<div class="acct-card"><table class="tbl"><tr><th>Name</th><th>Slug</th><th>Status</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><b><?= e($r['name']) ?></b></td><td class="muted"><?= e($r['slug']) ?></td><td><span class="badge <?= $r['status']?'delivered':'cancelled' ?>"><?= $r['status']?'On':'Off' ?></span></td><td><a class="btn btn-ghost btn-sm" href="?toggle=<?= $r['id'] ?>">Toggle</a> <a class="btn btn-ghost btn-sm" style="color:#c0392b" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')">✕</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="9" class="muted">No records yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
