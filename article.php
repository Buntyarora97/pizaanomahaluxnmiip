<?php
require_once __DIR__ . '/includes/functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT b.*, bc.name AS cat_name FROM blogs b LEFT JOIN blog_categories bc ON bc.id=b.category_id WHERE b.slug = ? AND b.status='published'");
$stmt->execute([$slug]);
$b = $stmt->fetch();
if (!$b) { http_response_code(404); include __DIR__ . '/404.php'; exit; }
$pageTitle = $b['meta_title'] ?: $b['title'] . ' — ' . setting('site_name');
$pageDesc = $b['meta_description'] ?: $b['excerpt'];
$canonical = SITE_URL . '/blog/' . $b['slug'];
include __DIR__ . '/includes/header.php';
?>
<article class="section"><div class="container" style="max-width:780px">
  <div class="breadcrumb"><a href="index.php">Home</a> / <a href="blog.php">Resources</a> / <?= e($b['title']) ?></div>
  <?php if ($b['featured_image']): ?><img src="<?= e($b['featured_image']) ?>" alt="<?= e($b['title']) ?>" style="border-radius:20px;margin:1.2rem 0"><?php endif; ?>
  <span class="sec-eyebrow"><?= e($b['cat_name'] ?: 'Article') ?></span>
  <h1 style="color:var(--navy);margin:.6rem 0 1.4rem;font-size:2rem"><?= e($b['title']) ?></h1>
  <div style="line-height:1.9;color:var(--ink)"><?= $b['content'] ?></div>
  <p class="muted" style="margin-top:2rem;font-size:.85rem"><?= $b['published_at'] ? 'Published ' . e(date('d M Y', strtotime($b['published_at']))) : '' ?><?= $b['author'] ? ' · By ' . e($b['author']) : '' ?></p>
</div></article>
<?php include __DIR__ . '/includes/footer.php'; ?>
