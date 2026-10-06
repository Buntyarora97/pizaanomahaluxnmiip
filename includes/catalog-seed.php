<?php

function mthCatalogCategoryId(PDO $pdo, string $name, string $slug, ?int $parentId, string $description, int $sortOrder): int
{
    $find = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
    $find->execute([$slug]);
    $existing = $find->fetchColumn();
    if ($existing !== false) {
        return (int)$existing;
    }

    $insert = $pdo->prepare(
        'INSERT INTO categories (parent_id, name, slug, description, sort_order, is_featured, status)
         VALUES (?, ?, ?, ?, ?, 1, 1)'
    );
    $insert->execute([$parentId, $name, $slug, $description, $sortOrder]);
    return (int)$pdo->lastInsertId();
}

function mthCatalogBrandId(PDO $pdo, string $name, string $slug): int
{
    $find = $pdo->prepare('SELECT id FROM brands WHERE slug = ?');
    $find->execute([$slug]);
    $existing = $find->fetchColumn();
    if ($existing !== false) {
        return (int)$existing;
    }

    $insert = $pdo->prepare('INSERT INTO brands (name, slug, status) VALUES (?, ?, 1)');
    $insert->execute([$name, $slug]);
    return (int)$pdo->lastInsertId();
}

function mthCatalogAttributeId(PDO $pdo, int $categoryId, string $name): int
{
    $slug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    $find = $pdo->prepare('SELECT id FROM attributes WHERE category_id = ? AND slug = ?');
    $find->execute([$categoryId, $slug]);
    $existing = $find->fetchColumn();
    if ($existing !== false) {
        return (int)$existing;
    }

    $insert = $pdo->prepare(
        "INSERT INTO attributes (category_id, name, slug, input_type, status) VALUES (?, ?, ?, 'text', 1)"
    );
    $insert->execute([$categoryId, $name, $slug]);
    return (int)$pdo->lastInsertId();
}

function mthSeedSuppliedCatalog(PDO $pdo): int
{
    $industrialId = mthCatalogCategoryId(
        $pdo,
        'Industrial & Electrical',
        'industrial-electrical',
        null,
        'Industrial and electrical supply.',
        1
    );
    $foodId = mthCatalogCategoryId(
        $pdo,
        'Agri, Food & Dry Fruits',
        'agri-food-dry-fruits',
        null,
        'Food, grains, nuts and beverage supply.',
        2
    );
    $applianceId = mthCatalogCategoryId(
        $pdo,
        'Kitchen & Home Appliances',
        'kitchen-home-appliances',
        null,
        'Kitchen and home appliance products.',
        3
    );

    $categoryIds = [
        'sandwich-makers' => mthCatalogCategoryId($pdo, 'Sandwich Makers', 'sandwich-makers', $applianceId, 'Sandwich makers listed in the supplied product catalogue.', 1),
        'mixer-grinder-motor-bases' => mthCatalogCategoryId($pdo, 'Mixer-Grinder Motor Bases', 'mixer-grinder-motor-bases', $applianceId, 'Mixer-grinder motor base products.', 2),
        'kitchen-chimneys' => mthCatalogCategoryId($pdo, 'Kitchen Chimneys', 'kitchen-chimneys', $applianceId, 'Kitchen chimney products.', 3),
        'rice-and-grains' => mthCatalogCategoryId($pdo, 'Rice & Grains', 'rice-and-grains', $foodId, 'Rice and grain products.', 5),
        'jams-spreads' => mthCatalogCategoryId($pdo, 'Jams & Spreads', 'jams-spreads', $foodId, 'Jams and spreads.', 6),
        'beverages' => mthCatalogCategoryId($pdo, 'Beverages', 'beverages', $foodId, 'Beverage products.', 4),
        'nuts-peanuts' => mthCatalogCategoryId($pdo, 'Nuts & Peanuts', 'nuts-peanuts', $foodId, 'Nuts and peanut products.', 2),
    ];

    $brands = [
        'Aasmi' => mthCatalogBrandId($pdo, 'Aasmi', 'aasmi'),
        'Jeenia' => mthCatalogBrandId($pdo, 'Jeenia', 'jeenia'),
        'Pluckers' => mthCatalogBrandId($pdo, 'Pluckers', 'pluckers'),
    ];

    $models = [
        ['32', 750, '28 × 25 cm', 2],
        ['31', 1000, '28.5 × 15.3 cm', 3],
        ['33', 750, '23.5 × 13.5 cm', 4],
        ['13', 800, '22.5 × 12.7 cm', 5],
        ['22', 800, '22.5 × 13.1 cm', 6],
        ['21', 800, '22.4 × 13 cm', 7],
        ['11', 800, '21.6 × 12.5 cm', 8],
        ['12', 800, '21.6 × 12.3 cm', 9],
        ['02', 800, '21.6 × 12.2 cm', 10],
        ['03', 800, '21.6 × 12.2 cm', 11],
        ['01', 800, '21 × 12.1 cm', 12],
        ['04', 800, '21 × 11.5 cm', 13],
        ['05', 750, null, 14],
        ['06', 750, '21 × 11.5 cm', 15],
        ['07', 750, '21.6 × 12.6 cm', 16],
    ];

    $products = [];
    foreach ($models as [$model, $power, $plateSize, $catalogPage]) {
        $modelCode = 'NXT-' . $model;
        $short = $modelCode . ' sandwich maker · ' . $power . ' W';
        $specs = [
            'Model' => $modelCode,
            'Rated power' => $power . ' W',
            'Top finish' => 'Stainless steel',
        ];
        if ($plateSize !== null) {
            $specs['Plate size'] = $plateSize;
        }
        $products[] = [
            'name' => 'Sandwich Maker · Model ' . $modelCode,
            'slug' => 'sandwich-maker-' . strtolower(str_replace('-', '', $modelCode)),
            'sku' => 'MTH-SM-' . str_replace('-', '', $modelCode),
            'category' => 'sandwich-makers',
            'brand' => null,
            'short' => $short,
            'description' => $modelCode . ' sandwich maker. The supplied catalogue lists a ' . $power . ' W rating, stainless-steel top'
                . ($plateSize ? ' and a ' . $plateSize . ' plate.' : '.')
                . ' Confirm the current configuration, price and availability when enquiring.',
            'image' => '/assets/images/products/sandwich-makers/nxt-' . $model . '.jpg',
            'featured' => $model === '32',
            'specs' => $specs,
        ];
    }

    $products = array_merge($products, [
        [
            'name' => 'Mixer-Grinder Motor Base · 550 W',
            'slug' => 'mixer-grinder-motor-base-550w',
            'sku' => 'MTH-MG-550W',
            'category' => 'mixer-grinder-motor-bases',
            'brand' => null,
            'short' => '550 W mixer-grinder motor base with three-speed control.',
            'description' => 'The supplied product photos show a 550 W mixer-grinder motor base with three-speed control. Confirm the current configuration, price and availability when enquiring.',
            'image' => '/assets/images/products/mixer-grinder/motor-base-550w.jpg',
            'gallery' => [
                '/assets/images/products/mixer-grinder/motor-base-550w.jpg',
                '/assets/images/products/mixer-grinder/motor-base-550w-pair.jpg',
                '/assets/images/products/mixer-grinder/motor-base-550w-side.jpg',
            ],
            'featured' => true,
            'specs' => ['Rated power' => '550 W', 'Speed control' => 'Three speed'],
        ],
        [
            'name' => 'Kitchen Chimney',
            'slug' => 'kitchen-chimney',
            'sku' => 'MTH-APP-KITCHEN-CHIMNEY',
            'category' => 'kitchen-chimneys',
            'brand' => null,
            'short' => 'Wall-mounted kitchen chimney.',
            'description' => 'The supplied product photos show a black wall-mounted kitchen chimney. Confirm the model, size, specifications, price and availability when enquiring.',
            'image' => '/assets/images/products/kitchen-chimneys/kitchen-chimney.jpg',
            'gallery' => [
                '/assets/images/products/kitchen-chimneys/kitchen-chimney.jpg',
                '/assets/images/products/kitchen-chimneys/kitchen-chimney-side.jpg',
                '/assets/images/products/kitchen-chimneys/kitchen-chimney-front.jpg',
                '/assets/images/products/kitchen-chimneys/kitchen-chimney-detail.jpg',
            ],
            'featured' => false,
            'specs' => [],
        ],
        [
            'name' => 'Aasmi 1121 Extra Long Basmati Rice',
            'slug' => 'aasmi-1121-extra-long-basmati-rice',
            'sku' => 'MTH-RICE-AASMI-1121',
            'category' => 'rice-and-grains',
            'brand' => $brands['Aasmi'],
            'short' => '1121 Extra Long Basmati Rice.',
            'description' => 'Product name is transcribed from the supplied pack. Confirm current lot, pack sizes, price and availability when enquiring.',
            'image' => '/assets/images/products/food/aasmi-rice.jpg',
            'featured' => false,
            'specs' => [],
        ],
        [
            'name' => 'Jeenia Extra Long Basmati Rice',
            'slug' => 'jeenia-extra-long-basmati-rice',
            'sku' => 'MTH-RICE-JEENIA-ELB',
            'category' => 'rice-and-grains',
            'brand' => $brands['Jeenia'],
            'short' => 'Extra Long Basmati Rice.',
            'description' => 'Product name is transcribed from the supplied pack. Confirm current lot, pack sizes, price and availability when enquiring.',
            'image' => '/assets/images/products/food/jeenia-rice.jpg',
            'featured' => true,
            'specs' => [],
        ],
        [
            'name' => 'Mango Jam · 450 g',
            'slug' => 'pluckers-mango-jam-450g',
            'sku' => 'MTH-JAM-PLUCKERS-MANGO-450',
            'category' => 'jams-spreads',
            'brand' => $brands['Pluckers'],
            'short' => 'Mango jam · 450 g reference pack.',
            'description' => 'The supplied product label lists 450 g. Confirm current batch, packaging, price and availability when enquiring.',
            'image' => '/assets/images/products/food/mango-jam-label.jpg',
            'featured' => true,
            'specs' => ['Pack size on supplied label' => '450 g'],
        ],
        [
            'name' => 'Pineapple Jam · 450 g',
            'slug' => 'pluckers-pineapple-jam-450g',
            'sku' => 'MTH-JAM-PLUCKERS-PINEAPPLE-450',
            'category' => 'jams-spreads',
            'brand' => $brands['Pluckers'],
            'short' => 'Pineapple jam · 450 g reference pack.',
            'description' => 'The supplied product label lists 450 g. Confirm current batch, packaging, price and availability when enquiring.',
            'image' => '/assets/images/products/food/pineapple-jam-label.jpg',
            'featured' => false,
            'specs' => ['Pack size on supplied label' => '450 g'],
        ],
        [
            'name' => 'Strawberry Jam · 450 g',
            'slug' => 'pluckers-strawberry-jam-450g',
            'sku' => 'MTH-JAM-PLUCKERS-STRAWBERRY-450',
            'category' => 'jams-spreads',
            'brand' => $brands['Pluckers'],
            'short' => 'Strawberry jam · 450 g reference pack.',
            'description' => 'The supplied product label lists 450 g. Confirm current batch, packaging, price and availability when enquiring.',
            'image' => '/assets/images/products/food/strawberry-jam-label.jpg',
            'featured' => false,
            'specs' => ['Pack size on supplied label' => '450 g'],
        ],
        [
            'name' => 'Fruit Cordial',
            'slug' => 'fruit-cordial',
            'sku' => 'MTH-BEV-FRUIT-CORDIAL',
            'category' => 'beverages',
            'brand' => null,
            'short' => 'Fruit cordial.',
            'description' => 'The supplied product photo identifies this as fruit cordial. Confirm brand, flavour, pack size, price and availability when enquiring.',
            'image' => '/assets/images/products/food/fruit-cordial.jpg',
            'featured' => false,
            'specs' => [],
        ],
        [
            'name' => 'Raw Peanuts',
            'slug' => 'raw-peanuts',
            'sku' => 'MTH-NUT-RAW-PEANUTS',
            'category' => 'nuts-peanuts',
            'brand' => null,
            'short' => 'Raw peanuts for bulk enquiry.',
            'description' => 'The supplied photo shows raw peanuts. Confirm grade, processing, pack size, price and availability when enquiring.',
            'image' => '/assets/images/products/food/raw-peanuts.jpg',
            'featured' => false,
            'specs' => [],
        ],
    ]);

    $insertProduct = $pdo->prepare(
        'INSERT INTO products
          (name, slug, sku, brand_id, category_id, short_description, description, price, mrp, stock,
           is_featured, is_bestseller, is_new, purchasable, quote_only, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, 0, NULL, NULL, ?, 0, 0, 0, 1, 1)'
    );
    $findProduct = $pdo->prepare('SELECT id FROM products WHERE slug = ?');
    $findImage = $pdo->prepare('SELECT id FROM product_images WHERE product_id = ? AND image = ?');
    $insertImage = $pdo->prepare(
        'INSERT INTO product_images (product_id, image, alt_text, sort_order) VALUES (?, ?, ?, ?)'
    );
    $findAttributeValue = $pdo->prepare(
        'SELECT id FROM product_attribute_values WHERE product_id = ? AND attribute_id = ?'
    );
    $insertAttributeValue = $pdo->prepare(
        'INSERT INTO product_attribute_values (product_id, attribute_id, value) VALUES (?, ?, ?)'
    );

    $created = 0;
    foreach ($products as $product) {
        $categoryId = $categoryIds[$product['category']];
        $brandId = $product['brand'] ? (int)$product['brand'] : null;
        $findProduct->execute([$product['slug']]);
        $productId = $findProduct->fetchColumn();

        if ($productId === false) {
            $insertProduct->execute([
                $product['name'],
                $product['slug'],
                $product['sku'],
                $brandId,
                $categoryId,
                $product['short'],
                $product['description'],
                $product['featured'] ? 1 : 0,
            ]);
            $productId = (int)$pdo->lastInsertId();
            $created++;
        } else {
            $productId = (int)$productId;
        }

        $images = $product['gallery'] ?? [$product['image']];
        foreach ($images as $order => $image) {
            $findImage->execute([$productId, $image]);
            if ($findImage->fetchColumn() === false) {
                $insertImage->execute([$productId, $image, $product['name'], $order]);
            }
        }

        foreach ($product['specs'] as $attributeName => $value) {
            $attributeId = mthCatalogAttributeId($pdo, $categoryId, $attributeName);
            $findAttributeValue->execute([$productId, $attributeId]);
            if ($findAttributeValue->fetchColumn() === false) {
                $insertAttributeValue->execute([$productId, $attributeId, $value]);
            }
        }
    }

    return $created;
}
