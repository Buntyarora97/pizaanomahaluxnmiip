<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Shop All Products — ' . setting('site_name');
$canonical = SITE_URL . '/shop';

$allCats = $pdo->query("SELECT id,name FROM categories WHERE status=1 ORDER BY sort_order")->fetchAll();
$allBrands = $pdo->query("SELECT id,name FROM brands WHERE status=1 ORDER BY name")->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container">
  <div class="breadcrumb"><a href="index.php">Home</a> / Shop</div>
  <h1>Shop Products</h1><p>Browse our complete catalogue — industrial &amp; electrical plus agri, food &amp; dry fruits.</p>
</div></section>
<section class="section"><div class="container">
  <button class="btn btn-ghost btn-sm filter-toggle">☰ Filters</button>
  <div class="shop-layout">
    <form class="filters" id="filterForm">
      <h5>Filters</h5>
      <div class="f-group"><h5>Category</h5>
        <?php foreach ($allCats as $c): ?><label><input type="checkbox" name="cat[]" value="<?= $c['id'] ?>"> <?= e($c['name']) ?></label><?php endforeach; ?>
      </div>
      <div class="f-group"><h5>Brand</h5>
        <?php foreach ($allBrands as $b): ?><label><input type="checkbox" name="brand[]" value="<?= $b['id'] ?>"> <?= e($b['name']) ?></label><?php endforeach; ?>
      </div>
      <div class="f-group"><h5>Price (₹)</h5>
        <div class="price-row"><input type="number" name="min" placeholder="Min"><input type="number" name="max" placeholder="Max"></div>
      </div>
      <div class="f-group"><h5>Availability</h5>
        <label><input type="checkbox" name="stock" value="1"> In stock only</label>
      </div>
    </form>
    <div>
      <div class="shop-toolbar">
        <span class="muted" id="shopCount"></span>
        <select id="sortSel">
          <option value="featured">Sort: Featured</option>
          <option value="new">Newest</option>
          <option value="plh">Price: Low to High</option>
          <option value="phl">Price: High to Low</option>
          <option value="name">Name A–Z</option>
        </select>
      </div>
      <div class="p-grid" id="shopGrid">
        <div class="skeleton" style="height:300px"></div><div class="skeleton" style="height:300px"></div>
        <div class="skeleton" style="height:300px"></div><div class="skeleton" style="height:300px"></div>
      </div>
    </div>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
