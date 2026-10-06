<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
$tot = cartTotals();
$items = [];
foreach ($tot['items'] as $i) {
    $items[] = ['id'=>$i['id'], 'name'=>$i['name'], 'quantity'=>$i['quantity'],
      'price'=>money($i['price']), 'line_total'=>money($i['price']*$i['quantity']),
      'image'=>productImage($i['image'])];
}
echo json_encode(['items'=>$items, 'subtotal'=>money($tot['subtotal'])]);
