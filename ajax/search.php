<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
$q = trim($_GET['q'] ?? '');
$out = [];
if (strlen($q) >= 2) {
    $like = "%$q%";
    $stmt = $pdo->prepare("SELECT p.name, p.slug, p.price, p.quote_only, c.name AS cat,
      (SELECT image FROM product_images WHERE product_id=p.id ORDER BY sort_order LIMIT 1) AS img
      FROM products p JOIN categories c ON c.id=p.category_id
      WHERE p.status=1 AND (p.name LIKE ? OR p.sku LIKE ? OR c.name LIKE ?) LIMIT 8");
    $stmt->execute([$like, $like, $like]);
    foreach ($stmt as $r) {
        $out[] = ['name'=>$r['name'], 'slug'=>$r['slug'], 'category'=>$r['cat'],
          'price'=>$r['quote_only'] ? 'Quote' : money($r['price']), 'image'=>productImage($r['img'])];
    }
}
echo json_encode(['results'=>$out]);
