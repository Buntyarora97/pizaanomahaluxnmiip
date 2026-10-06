<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $slug = trim($_POST['slug']) ?: strtolower(preg_replace('/[^a-z0-9]+/i','-',$_POST['title']));
    if ($id) {
        $pdo->prepare("UPDATE blogs SET category_id=?, title=?, slug=?, excerpt=?, content=?, author=?, tags=?, status=?, published_at=IF(?='published' AND published_at IS NULL, NOW(), published_at) WHERE id=?")
            ->execute([$_POST['category_id'] ?: null, trim($_POST['title']), $slug, trim($_POST['excerpt'] ?? ''), $_POST['content'], trim($_POST['author'] ?? '') ?: null, trim($_POST['tags'] ?? '') ?: null, $_POST['status'], $_POST['status'], $id]);
        flash('Article updated.');
    } else {
        $pdo->prepare("INSERT INTO blogs (category_id, title, slug, excerpt, content, author, tags, status, published_at) VALUES (?,?,?,?,?,?,?,?,IF(?='published', NOW(), NULL))")
            ->execute([$_POST['category_id'] ?: null, trim($_POST['title']), $slug . '-' . substr(bin2hex(random_bytes(2)),0,4), trim($_POST['excerpt'] ?? ''), $_POST['content'], trim($_POST['author'] ?? '') ?: null, trim($_POST['tags'] ?? '') ?: null, $_POST['status'], $_POST['status']]);
        flash('Article created.');
    }
    redirect('blogs.php');
}
if (isset($_GET['toggle'])) { $pdo->prepare("UPDATE blogs SET status = IF(status='published','draft','published') WHERE id = ?")->execute([(int)$_GET['toggle']]); redirect('blogs.php'); }
if (isset($_GET['delete'])) { $pdo->prepare('DELETE FROM blogs WHERE id = ?')->execute([(int)$_GET['delete']]); redirect('blogs.php'); }
$edit = null;
if (!empty($_GET['edit'])) { $s = $pdo->prepare('SELECT * FROM blogs WHERE id = ?'); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch(); }
$blogs = $pdo->query('SELECT b.*, bc.name AS cat_name FROM blogs b LEFT JOIN blog_categories bc ON bc.id=b.category_id ORDER BY b.id DESC')->fetchAll();
$cats = $pdo->query('SELECT * FROM blog_categories ORDER BY name')->fetchAll();
?>
<div class="admin-top"><h1>Blog / Resources</h1><a href="blogs.php?new=1" class="btn btn-primary btn-sm">+ New Article</a></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<?php if ($edit || isset($_GET['new'])): ?>
<div class="acct-card"><h3><?= $edit ? 'Edit Article' : 'New Article' ?></h3>
<form method="post"><?= csrf_field() ?><?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
  <div class="form-row"><div class="field"><label>Title *</label><input name="title" required value="<?= e($edit['title'] ?? '') ?>"></div>
  <div class="field"><label>Category</label><select name="category_id"><option value="">—</option><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div></div>
  <div class="field"><label>Excerpt</label><textarea name="excerpt"><?= e($edit['excerpt'] ?? '') ?></textarea></div>
  <div class="field"><label>Content (HTML allowed) *</label><textarea name="content" style="min-height:220px"><?= e($edit['content'] ?? '') ?></textarea></div>
  <div class="form-row"><div class="field"><label>Author</label><input name="author" value="<?= e($edit['author'] ?? '') ?>"></div>
  <div class="field"><label>Tags (comma separated)</label><input name="tags" value="<?= e($edit['tags'] ?? '') ?>"></div>
  <div class="field"><label>Status</label><select name="status"><option value="draft" <?= ($edit['status'] ?? '')==='draft'?'selected':'' ?>>Draft</option><option value="published" <?= ($edit['status'] ?? '')==='published'?'selected':'' ?>>Published</option></select></div></div>
  <button class="btn btn-primary">Save Article</button> <a href="blogs.php" class="btn btn-ghost">Cancel</a>
</form></div>
<?php endif; ?>
<div class="acct-card"><table class="tbl"><tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th></th></tr>
<?php foreach ($blogs as $b): ?><tr><td><b><?= e($b['title']) ?></b></td><td><?= e($b['cat_name'] ?: '—') ?></td>
<td><span class="badge <?= $b['status']==='published'?'delivered':'pending' ?>"><?= e($b['status']) ?></span></td>
<td><?= $b['published_at'] ? e(date('d M Y', strtotime($b['published_at']))) : '—' ?></td>
<td><a class="btn btn-ghost btn-sm" href="blogs.php?edit=<?= $b['id'] ?>">Edit</a> <a class="btn btn-ghost btn-sm" href="?toggle=<?= $b['id'] ?>">Pub/Unpub</a> <a class="btn btn-ghost btn-sm" style="color:#c0392b" href="?delete=<?= $b['id'] ?>" onclick="return confirm('Delete article?')">✕</a></td></tr><?php endforeach; ?>
<?php if (!$blogs): ?><tr><td colspan="5" class="muted">No articles yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
