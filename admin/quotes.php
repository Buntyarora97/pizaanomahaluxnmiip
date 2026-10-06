<?php
require_once __DIR__ . '/includes/admin-header.php';
if (!empty($_GET['status']) && !empty($_GET['id'])) {
    $pdo->prepare('UPDATE bulk_quotes SET status = ? WHERE id = ?')->execute([$_GET['status'], (int)$_GET['id']]);
    flash('Quote updated.'); redirect('quotes.php');
}
$quotes = $pdo->query('SELECT q.*, (SELECT GROUP_CONCAT(p.name SEPARATOR ", ") FROM bulk_quote_items bi LEFT JOIN products p ON p.id=bi.product_id WHERE bi.quote_id=q.id) AS products FROM bulk_quotes q ORDER BY q.id DESC')->fetchAll();
?>
<div class="admin-top"><h1>Bulk Quotes (<?= count($quotes) ?>)</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><table class="tbl"><tr><th>Ref</th><th>Company</th><th>Contact</th><th>Products</th><th>Qty</th><th>Status</th><th></th></tr>
<?php foreach ($quotes as $q):
  $qi = $pdo->prepare('SELECT SUM(quantity) FROM bulk_quote_items WHERE quote_id = ?'); $qi->execute([$q['id']]); $qty = $qi->fetchColumn(); ?>
<tr><td><b><?= e($q['quote_number']) ?></b><br><span class="muted"><?= e(date('d M Y', strtotime($q['created_at']))) ?></span></td>
<td><?= e($q['company']) ?></td><td><?= e($q['contact_person']) ?><br><span class="muted"><?= e($q['phone']) ?> · <?= e($q['email']) ?></span></td>
<td><?= e($q['products'] ?: 'General enquiry') ?></td><td><?= $qty ?: '—' ?></td>
<td><span class="badge <?= $q['status']==='new'?'pending':'delivered' ?>"><?= e($q['status']) ?></span></td>
<td><a href="?id=<?= $q['id'] ?>&status=contacted" class="btn btn-ghost btn-sm">Contacted</a> <a href="?id=<?= $q['id'] ?>&status=quoted" class="btn btn-ghost btn-sm">Quoted</a> <a href="?id=<?= $q['id'] ?>&status=converted" class="btn btn-primary btn-sm">Converted</a></td></tr>
<?php endforeach; if (!$quotes): ?><tr><td colspan="7" class="muted">No bulk quotes yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
