<?php
require_once __DIR__ . '/includes/admin-header.php';
require_once __DIR__ . '/../../includes/csrf.php';
if (!adminCan('products.manage') && $_SESSION['admin']['role_id'] != 1) { echo '<div class="alert error">Permission denied.</div>'; include __DIR__ . '/includes/admin-footer.php'; exit; }

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        trim($_POST['name'] ?? ''),
        trim($_POST['slug'] ?? '') ?: strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $_POST['name'] ?? ''), '-')),
        trim($_POST['sku'] ?? ''), (int)($_POST['brand_id'] ?? 0) ?: null, (int)($_POST['category_id'] ?? 0),
        trim($_POST['short_description'] ?? ''), trim($_POST['description'] ?? ''),
        (float)($_POST['price'] ?? 0), ($_POST['mrp'] ?? '') !== '' ? (float)$_POST['mrp'] : null,
        trim($_POST['hsn'] ?? '') ?: null, (int)$_POST['stock'], (int)$_POST['low_stock'],
        !empty($_POST['is_featured']) ? 1 : 0, !empty($_POST['is_bestseller']) ? 1 : 0,
        !empty($_POST['is_new']) ? 1 : 0, !empty($_POST['purchasable']) ? 1 : 0,
        !empty($_POST['quote_only']) ? 1 : 0, !empty($_POST['status']) ? 1 : 0
    ];
    if ($id) {
        $pdo->prepare('UPDATE products SET name=?, slug=?, sku=?, brand_id=?, category_id=?, short_description=?, description=?, price=?, mrp=?, hsn=?, stock=?, low_stock_threshold=?, is_featured=?, is_bestseller=?, is_new=?, purchasable=?, quote_only=?, status=? WHERE id=?')
            ->execute([...$data, $id]);
        flash('Product updated.');
    } else {
        $pdo->prepare('INSERT INTO products (name, slug, sku, brand_id, category_id, short_description, description, price, mrp, hsn, stock, low_stock_threshold, is_featured, is_bestseller, is_new, purchasable, quote_only, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute($data);
        $id = $pdo->lastInsertId();
        flash('Product created. Now add images below.');
    }
    // Handle image upload
    if (!empty($_FILES['image']['name']) && $id) {
        $f = $_FILES['image'];
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (isset($allowed[$mime]) && $f['size'] < 3 * 1024 * 1024 && is_uploaded_file($f['tmp_name'])) {
            $fname = 'p' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $fname)) {
                $pdo->prepare('INSERT INTO product_images (product_id, image, alt_text) VALUES (?,?,?)')->execute([$id, $fname, trim($_POST['name'])]);
            }
        }
    }
    redirect('products.php?edit=' . $id);
}
// Delete
if (isset($_GET['delete'])) {
    $pdo->prepare('UPDATE products SET status = 0 WHERE id = ?')->execute([(int)$_GET['delete']]);
    flash('Product deactivated.');
    redirect('products.php');
}
$edit = null;
if (!empty($_GET['edit'])) {
    $s = $pdo->prepare('SELECT * FROM products WHERE id = ?'); $s->execute([(int)$_GET['edit']]); $edit = $s->fetch();
    if ($edit) { $imgs = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ?'); $imgs->execute([$edit['id']]); $pimages = $imgs->fetchAll(); }
}
$products = $pdo->query("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC LIMIT 100")->fetchAll();
$cats = $pdo->query("SELECT id, name FROM categories WHERE status=1 ORDER BY name")->fetchAll();
$brands = $pdo->query('SELECT id, name FROM brands WHERE status=1 ORDER BY name')->fetchAll();
?>
<div class="admin-top"><h1>Products</h1><a href="products.php?new=1" class="btn btn-primary btn-sm">+ Add Product</a></div>
<?php if ($fl): ?><div class="alert <?= e($fl['type']) ?>"><?= e($fl['msg']) ?></div><?php endif; ?>

<?php if ($edit || isset($_GET['new'])): ?>
<div class="acct-card"><h3><?= $edit ? 'Edit: ' . e($edit['name']) : 'New Product' ?></h3>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
  <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
  <div class="form-row"><div class="field"><label>Name *</label><input name="name" required value="<?= e($edit['name'] ?? '') ?>"></div><div class="field"><label>Slug</label><input name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="auto-generated"></div></div>
  <div class="form-row"><div class="field"><label>SKU *</label><input name="sku" required value="<?= e($edit['sku'] ?? '') ?>"></div><div class="field"><label>Category *</label><select name="category_id" required><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div></div>
  <div class="form-row"><div class="field"><label>Brand</label><select name="brand_id"><option value="">—</option><?php foreach ($brands as $b): ?><option value="<?= $b['id'] ?>" <?= ($edit['brand_id'] ?? '')==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
  <div class="field"><label>HSN</label><input name="hsn" value="<?= e($edit['hsn'] ?? '') ?>"></div></div>
  <div class="form-row"><div class="field"><label>Price (₹) *</label><input type="number" step="0.01" name="price" required value="<?= e($edit['price'] ?? '') ?>"></div><div class="field"><label>MRP</label><input type="number" step="0.01" name="mrp" value="<?= e($edit['mrp'] ?? '') ?>"></div></div>
  <div class="form-row"><div class="field"><label>Stock *</label><input type="number" name="stock" value="<?= e($edit['stock'] ?? 0) ?>"></div><div class="field"><label>Low Stock Alert</label><input type="number" name="low_stock" value="<?= e($edit['low_stock_threshold'] ?? 5) ?>"></div></div>
  <div class="field"><label>Short Description</label><textarea name="short_description"><?= e($edit['short_description'] ?? '') ?></textarea></div>
  <div class="field"><label>Full Description</label><textarea name="description" style="min-height:160px"><?= e($edit['description'] ?? '') ?></textarea></div>
  <div class="field"><label>Product Image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
  <div style="display:flex;gap:1.2rem;flex-wrap:wrap;margin:.6rem 0 1.2rem">
    <?php foreach (['is_featured'=>'Featured','is_bestseller'=>'Best Seller','is_new'=>'New Arrival','purchasable'=>'Purchasable Online','quote_only'=>'Quote Only','status'=>'Active'] as $k=>$l): ?>
    <label style="display:flex;gap:.4rem;align-items:center;font-size:.88rem"><input type="checkbox" name="<?= $k ?>" <?= ($edit[$k] ?? ($k==='purchasable'||$k==='status' ? 1 : 0)) ? 'checked' : '' ?>> <?= $l ?></label>
    <?php endforeach; ?>
  </div>
  <button class="btn btn-primary">Save Product</button> <a href="products.php" class="btn btn-ghost">Cancel</a>
</form>
<?php if (!empty($pimages)): ?><h3 style="margin-top:1.6rem">Images</h3>
<div style="display:flex;gap:.8rem;flex-wrap:wrap"><?php foreach ($pimages as $im): ?><img src="<?= e(UPLOAD_URL . $im['image']) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:10px"><?php endforeach; ?></div><?php endif; ?>
</div>
<?php endif; ?>

<div class="acct-card"><h3>All Products (<?= count($products) ?>)</h3>
<table class="tbl"><tr><th>Product</th><th>SKU</th><th>Category</th><th>Price</th><th>Stock</th><th>Flags</th><th></th></tr>
<?php foreach ($products as $p): ?><tr>
<td><b><?= e($p['name']) ?></b></td><td><?= e($p['sku']) ?></td><td><?= e($p['cat_name']) ?></td>
<td><?= money($p['price']) ?></td>
<td><?= $p['stock'] <= $p['low_stock_threshold'] ? '<b style="color:#c0392b">' . $p['stock'] . '</b>' : $p['stock'] ?></td>
<td><?= $p['is_featured'] ? '<span class="badge placed">F</span> ' : '' ?><?= $p['quote_only'] ? '<span class="badge pending">QUOTE</span>' : '' ?><?= !$p['status'] ? '<span class="badge cancelled">OFF</span>' : '' ?></td>
<td><a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
<a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-ghost btn-sm" style="color:#c0392b" onclick="return confirm('Deactivate this product?')">Delete</a></td>
</tr><?php endforeach; ?>
<?php if (!$products): ?><tr><td colspan="7" class="muted">No products yet — click "Add Product".</td></tr><?php endif; ?></table>
</div>
<?php include __DIR__ . '/includes/admin-footer.php'; ?>
