<?php
require_once __DIR__ . '/includes/functions.php';
$cat = $_GET['cat'] ?? null;
$sql = "SELECT b.*, bc.name AS cat_name, bc.slug AS cat_slug FROM blogs b LEFT JOIN blog_categories bc ON bc.id=b.category_id WHERE b.status='published'";
$params = [];
if ($cat) { $sql .= " AND bc.slug = ?"; $params[] = $cat; }
$sql .= " ORDER BY b.published_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$blogs = $stmt->fetchAll();
$cats = $pdo->query('SELECT * FROM blog_categories')->fetchAll();
$pageTitle = 'Resources & Blog — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="index.php">Home</a> / Resources</div><h1>Guides &amp; Insights</h1><p>Product guides, procurement knowledge and trade updates.</p></div></section>
<section class="section"><div class="container">
  <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:2rem">
    <a href="blog.php" class="chip" style="background:<?= !$cat?'var(--navy)':'#fff' ?>;color:<?= !$cat?'#fff':'var(--navy)' ?>;border:1px solid var(--line)">All</a>
    <?php foreach ($cats as $c): ?><a href="blog.php?cat=<?= e($c['slug']) ?>" class="chip" style="background:<?= $cat===$c['slug']?'var(--navy)':'#fff' ?>;color:<?= $cat===$c['slug']?'#fff':'var(--navy)' ?>;border:1px solid var(--line)"><?= e($c['name']) ?></a><?php endforeach; ?>
  </div>
  <?php if (!$blogs): ?><p class="muted" style="text-align:center;padding:3rem 0">Articles coming soon.</p>
  <?php else: ?><div class="blog-grid">
    <?php foreach ($blogs as $b): ?><a href="article.php?slug=<?= e($b['slug']) ?>" class="blog-card reveal">
      <?php if ($b['featured_image']): ?><img src="<?= e($b['featured_image']) ?>" alt="<?= e($b['title']) ?>" loading="lazy"><?php endif; ?>
      <div class="bc-body"><span class="cat"><?= e($b['cat_name'] ?: 'Article') ?></span><h4><?= e($b['title']) ?></h4><p><?= e($b['excerpt']) ?></p></div>
    </a><?php endforeach; ?>
  </div><?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
