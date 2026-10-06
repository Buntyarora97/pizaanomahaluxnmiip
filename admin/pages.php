<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('UPDATE pages SET title=?, content=?, meta_title=?, meta_description=? WHERE id=?')
        ->execute([trim($_POST['title']), $_POST['content'], trim($_POST['meta_title'] ?? '') ?: null, trim($_POST['meta_description'] ?? '') ?: null, (int)$_POST['id']]);
    flash('Page saved.');
    redirect('pages.php?edit=' . (int)$_POST['id']);
}
$edit = null;
if (!empty($_GET['edit'])) { $s = $pdo->prepare('SELECT * FROM pages WHERE id = ?'); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$pages = $pdo->query('SELECT * FROM pages ORDER BY id')->fetchAll();
?>
<div class="admin-top"><h1>Pages (Legal & Static)</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<?php if ($edit): ?>
<div class="acct-card"><h3>Edit: <?= e($edit['title']) ?> <span class="muted">(/page.php?slug=<?= e($edit['slug']) ?>)</span></h3>
<form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $edit['id'] ?>">
  <div class="field"><label>Title</label><input name="title" value="<?= e($edit['title']) ?>"></div>
  <div class="field"><label>Content (HTML allowed)</label><textarea name="content" style="min-height:260px"><?= e($edit['content']) ?></textarea></div>
  <div class="form-row"><div class="field"><label>Meta Title</label><input name="meta_title" value="<?= e($edit['meta_title'] ?? '') ?>"></div><div class="field"><label>Meta Description</label><input name="meta_description" value="<?= e($edit['meta_description'] ?? '') ?>"></div></div>
  <button class="btn btn-primary">Save Page</button> <a href="pages.php" class="btn btn-ghost">Back</a>
</form></div>
<?php endif; ?>
<div class="acct-card"><table class="tbl"><tr><th>Page</th><th>Slug</th><th></th></tr>
<?php foreach ($pages as $p): ?><tr><td><b><?= e($p['title']) ?></b></td><td class="muted"><?= e($p['slug']) ?></td><td><a class="btn btn-ghost btn-sm" href="pages.php?edit=<?= $p['id'] ?>">Edit</a> <a class="btn btn-ghost btn-sm" href="<?= SITE_URL ?>/page.php?slug=<?= e($p['slug']) ?>" target="_blank">View</a></td></tr><?php endforeach; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
