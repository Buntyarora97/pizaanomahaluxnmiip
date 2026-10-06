<?php
require_once __DIR__ . '/includes/admin-header.php';
if (isset($_GET['approve'])) { $pdo->prepare('UPDATE reviews SET status = 1 WHERE id = ?')->execute([(int)$_GET['approve']]); flash('Review published.'); redirect('reviews.php'); }
if (isset($_GET['delete'])) { $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([(int)$_GET['delete']]); redirect('reviews.php'); }
$reviews = $pdo->query('SELECT r.*, p.name AS pname, c.full_name FROM reviews r JOIN products p ON p.id=r.product_id JOIN customers c ON c.id=r.customer_id ORDER BY r.status ASC, r.id DESC')->fetchAll();
?>
<div class="admin-top"><h1>Reviews Moderation</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><table class="tbl"><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Review</th><th>Status</th><th></th></tr>
<?php foreach ($reviews as $r): ?><tr><td><?= e($r['pname']) ?></td><td><?= e($r['full_name']) ?></td><td>★ <?= $r['rating'] ?></td><td style="max-width:280px"><?= e($r['review']) ?></td>
<td><span class="badge <?= $r['status']?'delivered':'pending' ?>"><?= $r['status']?'Published':'Pending' ?></span></td>
<td><?php if (!$r['status']): ?><a class="btn btn-primary btn-sm" href="?approve=<?= $r['id'] ?>">Approve</a><?php endif; ?> <a class="btn btn-ghost btn-sm" style="color:#c0392b" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete review?')">✕</a></td></tr><?php endforeach; ?>
<?php if (!$reviews): ?><tr><td colspan="6" class="muted">No reviews yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
