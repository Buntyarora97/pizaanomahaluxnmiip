<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('INSERT INTO testimonials (name, company, rating, review, status) VALUES (?,?,?,?,0)')
        ->execute([trim($_POST['name']), trim($_POST['company'] ?? '') ?: null, max(1, min(5, (int)$_POST['rating'])), trim($_POST['review'])]);
    flash('Testimonial added (pending approval).');
    redirect('testimonials.php');
}
if (isset($_GET['approve'])) { $pdo->prepare('UPDATE testimonials SET status = 1 WHERE id = ?')->execute([(int)$_GET['approve']]); flash('Published.'); redirect('testimonials.php'); }
if (isset($_GET['delete'])) { $pdo->prepare('DELETE FROM testimonials WHERE id = ?')->execute([(int)$_GET['delete']]); redirect('testimonials.php'); }
$t = $pdo->query('SELECT * FROM testimonials ORDER BY status ASC, id DESC')->fetchAll();
?>
<div class="admin-top"><h1>Testimonials</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Add Testimonial (genuine only)</h3>
<form method="post" style="display:grid;grid-template-columns:1fr 1fr 80px 2fr auto;gap:.8rem;align-items:end"><?= csrf_field() ?>
  <div class="field"><label>Name *</label><input name="name" required></div>
  <div class="field"><label>Company</label><input name="company"></div>
  <div class="field"><label>Rating</label><input type="number" name="rating" min="1" max="5" value="5"></div>
  <div class="field"><label>Review *</label><input name="review" required></div>
  <button class="btn btn-primary">Add</button>
</form></div>
<div class="acct-card"><table class="tbl"><tr><th>Name</th><th>Company</th><th>Rating</th><th>Review</th><th>Status</th><th></th></tr>
<?php foreach ($t as $x): ?><tr><td><b><?= e($x['name']) ?></b></td><td><?= e($x['company']) ?></td><td>★ <?= $x['rating'] ?></td><td class="muted" style="max-width:300px"><?= e($x['review']) ?></td>
<td><span class="badge <?= $x['status']?'delivered':'pending' ?>"><?= $x['status']?'Published':'Pending' ?></span></td>
<td><?php if (!$x['status']): ?><a class="btn btn-primary btn-sm" href="?approve=<?= $x['id'] ?>">Approve</a><?php endif; ?> <a class="btn btn-ghost btn-sm" style="color:#c0392b" href="?delete=<?= $x['id'] ?>" onclick="return confirm('Delete?')">✕</a></td></tr><?php endforeach; ?>
<?php if (!$t): ?><tr><td colspan="6" class="muted">No testimonials yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
