<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('INSERT INTO videos (title, category, video_type, video_url, thumbnail, attached_to, sort_order) VALUES (?,?,?,?,?,?,?)')
        ->execute([trim($_POST['title']), trim($_POST['category'] ?? '') ?: null, $_POST['video_type'], trim($_POST['video_url']), trim($_POST['thumbnail'] ?? '') ?: null, trim($_POST['attached_to'] ?? '') ?: null, (int)($_POST['sort_order'] ?? 0)]);
    flash('Video added.');
    redirect('videos.php');
}
if (isset($_GET['delete'])) { $pdo->prepare('DELETE FROM videos WHERE id = ?')->execute([(int)$_GET['delete']]); redirect('videos.php'); }
$videos = $pdo->query('SELECT * FROM videos ORDER BY sort_order, id DESC')->fetchAll();
?>
<div class="admin-top"><h1>Videos</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Add Video</h3>
<form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.8rem;align-items:end"><?= csrf_field() ?>
  <div class="field"><label>Title *</label><input name="title" required></div>
  <div class="field"><label>Category</label><input name="category" placeholder="Product / Industrial / Food"></div>
  <div class="field"><label>Type</label><select name="video_type"><option value="youtube">YouTube</option><option value="vimeo">Vimeo</option><option value="mp4">MP4</option></select></div>
  <div class="field"><label>Video URL *</label><input name="video_url" required></div>
  <div class="field"><label>Thumbnail URL</label><input name="thumbnail"></div>
  <div class="field"><label>Show On</label><select name="attached_to"><option value="">Videos page only</option><option value="home">Homepage</option></select></div>
  <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
  <button class="btn btn-primary">Add</button>
</form></div>
<div class="acct-card"><table class="tbl"><tr><th>Title</th><th>Type</th><th>URL</th><th>Location</th><th></th></tr>
<?php foreach ($videos as $v): ?><tr><td><b><?= e($v['title']) ?></b></td><td><?= e($v['video_type']) ?></td><td class="muted" style="max-width:220px;overflow:hidden;text-overflow:ellipsis"><?= e($v['video_url']) ?></td><td><?= e($v['attached_to'] ?: 'videos') ?></td><td><a class="btn btn-ghost btn-sm" style="color:#c0392b" href="?delete=<?= $v['id'] ?>" onclick="return confirm('Delete?')">✕</a></td></tr><?php endforeach; ?>
<?php if (!$videos): ?><tr><td colspan="5" class="muted">No videos yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
