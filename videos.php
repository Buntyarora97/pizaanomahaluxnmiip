<?php
require_once __DIR__ . '/includes/functions.php';
$cat = $_GET['cat'] ?? null;
$sql = "SELECT * FROM videos WHERE status=1"; $params = [];
if ($cat) { $sql .= " AND category = ?"; $params[] = $cat; }
$sql .= " ORDER BY sort_order, id DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$videos = $stmt->fetchAll();
$cats = $pdo->query('SELECT DISTINCT category FROM videos WHERE category IS NOT NULL')->fetchAll(PDO::FETCH_COLUMN);
$pageTitle = 'Videos — ' . setting('site_name');
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><div class="breadcrumb"><a href="index.php">Home</a> / Videos</div><h1>Videos</h1><p>Product videos, industrial guides, food & business content.</p></div></section>
<section class="section"><div class="container">
  <?php if ($cats): ?><div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:2rem">
    <a href="videos.php" class="chip" style="background:<?= !$cat?'var(--navy)':'#fff' ?>;color:<?= !$cat?'#fff':'var(--navy)' ?>;border:1px solid var(--line)">All</a>
    <?php foreach ($cats as $c): ?><a href="videos.php?cat=<?= e($c) ?>" class="chip" style="background:<?= $cat===$c?'var(--navy)':'#fff' ?>;color:<?= $cat===$c?'#fff':'var(--navy)' ?>;border:1px solid var(--line)"><?= e(ucwords($c)) ?></a><?php endforeach; ?>
  </div><?php endif; ?>
  <?php if (!$videos): ?><p class="muted" style="text-align:center;padding:3rem 0">Videos coming soon.</p>
  <?php else: ?><div class="blog-grid">
    <?php foreach ($videos as $v): ?>
    <a href="<?= e($v['video_url']) ?>" target="_blank" rel="noopener" class="blog-card reveal">
      <div style="position:relative"><img src="<?= e($v['thumbnail'] ?: SITE_URL.'/assets/images/placeholder.svg') ?>" alt="<?= e($v['title']) ?>" loading="lazy" style="height:200px;width:100%;object-fit:cover"><span style="position:absolute;inset:0;display:grid;place-items:center;font-size:2.4rem;color:#fff;text-shadow:0 4px 20px rgba(0,0,0,.6)">▶</span></div>
      <div class="bc-body"><span class="cat"><?= e(ucwords($v['category'] ?: 'Video')) ?></span><h4><?= e($v['title']) ?></h4></div>
    </a>
    <?php endforeach; ?>
  </div><?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
