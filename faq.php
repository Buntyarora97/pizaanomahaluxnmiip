<?php
require_once __DIR__ . '/includes/functions.php';
$faqs = $pdo->query('SELECT * FROM faqs WHERE status=1 ORDER BY sort_order')->fetchAll();
$pageTitle = 'FAQ — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="index.php">Home</a> / FAQ</div><h1>Frequently Asked Questions</h1></div></section>
<section class="section"><div class="container" style="max-width:760px">
  <?php if (!$faqs): ?><p class="muted" style="text-align:center">FAQs coming soon. <a href="contact.php" style="color:var(--navy2)">Contact us →</a></p>
  <?php else: foreach ($faqs as $f): ?><details class="acct-card" style="padding:1.2rem 1.6rem"><summary style="font-weight:600;color:var(--navy);cursor:pointer"><?= e($f['question']) ?></summary><p class="muted" style="margin-top:.8rem;line-height:1.8"><?= e($f['answer']) ?></p></details><?php endforeach; endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
