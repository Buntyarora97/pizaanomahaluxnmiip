<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $pdo->prepare('INSERT INTO faqs (question, answer, category, sort_order) VALUES (?,?,?,?)')
        ->execute([trim($_POST['question']), trim($_POST['answer']), trim($_POST['category'] ?? '') ?: null, (int)($_POST['sort_order'] ?? 0)]);
    flash('FAQ added.');
    redirect('faqs.php');
}
if (isset($_GET['delete'])) { $pdo->prepare('DELETE FROM faqs WHERE id = ?')->execute([(int)$_GET['delete']]); redirect('faqs.php'); }
$faqs = $pdo->query('SELECT * FROM faqs ORDER BY sort_order, id')->fetchAll();
?>
<div class="admin-top"><h1>FAQs</h1></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>
<div class="acct-card"><h3>Add FAQ</h3>
<form method="post" style="display:grid;grid-template-columns:1fr 2fr auto;gap:.8rem;align-items:end"><?= csrf_field() ?>
  <div class="field"><label>Question *</label><input name="question" required></div>
  <div class="field"><label>Answer *</label><input name="answer" required></div>
  <button class="btn btn-primary">Add</button>
</form></div>
<div class="acct-card"><table class="tbl"><tr><th>Question</th><th>Answer</th><th></th></tr>
<?php foreach ($faqs as $f): ?><tr><td><b><?= e($f['question']) ?></b></td><td class="muted" style="max-width:400px"><?= e($f['answer']) ?></td><td><a class="btn btn-ghost btn-sm" style="color:#c0392b" href="?delete=<?= $f['id'] ?>" onclick="return confirm('Delete?')">✕</a></td></tr><?php endforeach; ?>
<?php if (!$faqs): ?><tr><td colspan="3" class="muted">No FAQs yet.</td></tr><?php endif; ?></table></div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
