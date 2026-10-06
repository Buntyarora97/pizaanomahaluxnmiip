<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $_POST['name'])), '-');
    $pdo->prepare('INSERT INTO categories (parent_id, name, slug, description, sort_order) VALUES (?,?,?,?,?)')
        ->execute([$_POST['parent_id'] ?: null, trim($_POST['name']), $slug . '-' . substr(bin2hex(random_bytes(3)), 0, 5), trim($_POST['description'] ?? '') ?: null, (int)($_POST['sort_order'] ?? 0)]);
    flash('Saved successfully.');
    redirect(basename($_SERVER['PHP_SELF']));
}
if (isset($_GET['toggle'])) {
    $pdo->prepare('UPDATE categories SET status = 1 - status WHERE id = ?')->execute([(int)$_GET['toggle']]);
    redirect(basename($_SERVER['PHP_SELF']));
}
if (isset($_GET['delete'])) {
    $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([(int)$_GET['delete']]);
    flash('Deleted.'); redirect(basename($_SERVER['PHP_SELF']));
}
$rows = $pdo->query( + table_sql + r)->fetchAll();
?>
<div class="admin-top"><h1>Categories</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Add New</h3>
<form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.8rem;align-items:end"><?= csrf_field() ?>
<div class="field"><label>Name *</label><input name="name" required></div><div class="field"><label>Parent Category</label><select name="parent_id"><option value="">— Top Level —</option><?php foreach ($pdo->query("SELECT id,name FROM categories WHERE parent_id IS NULL") as $pc): ?><option value="<?= $pc['id'] ?>"><?= e($pc['name']) ?></option><?php endforeach; ?></select></div><div class="field"><label>Description</label><input name="description"></div><div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
<button class="btn btn-primary">Save</button></form>
<p class="muted" style="font-size:.78rem;margin-top:.6rem">For images: upload via FTP/cPanel to assets/uploads and set in DB, or extend with the media library.</p></div>
<div class="acct-card"><table class="tbl"><tr><th>Name</th><th>Slug</th><th>Parent</th><th>Order</th><th>Status</th><th></th></tr>
<?php foreach ($rows as $r): ?><tr><td><b><?= e($r['name']) ?></b></td><td class="muted"><?= e($r['slug']) ?></td><td><?= e($r['parent_name'] ?: '—') ?></td><td><?= $r['sort_order'] ?></td><td><span class="badge <?= $r['status']?'delivered':'cancelled' ?>"><?= $r['status']?'On':'Off' ?></span></td><td><a class="btn btn-ghost btn-sm" href="?toggle=<?= $r['id'] ?>">Toggle</a> <a class="btn btn-ghost btn-sm" style="color:#c0392b" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete?')">✕</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="9" class="muted">No records yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
