<?php
require_once __DIR__ . '/includes/functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM pages WHERE slug = ? AND status = 1');
$stmt->execute([$slug]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); include __DIR__ . '/404.php'; exit; }
$pageTitle = $p['meta_title'] ?: $p['title'] . ' — ' . setting('site_name');
$pageDesc = $p['meta_description'] ?: strip_tags(substr($p['content'], 0, 160));
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="index.php">Home</a> / <?= e($p['title']) ?></div><h1><?= e($p['title']) ?></h1></div></section>
<section class="section"><div class="container" style="max-width:820px;line-height:1.9;color:var(--muted)"><?= $p['content'] ?></div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
