<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/xml; charset=utf-8');
$urls = [SITE_URL . '/', SITE_URL . '/shop', SITE_URL . '/about', SITE_URL . '/business', SITE_URL . '/import-export', SITE_URL . '/contact', SITE_URL . '/blog', SITE_URL . '/videos', SITE_URL . '/faq'];
foreach ($pdo->query("SELECT slug FROM categories WHERE status=1") as $c) $urls[] = SITE_URL . '/category/' . $c['slug'];
foreach ($pdo->query("SELECT slug, updated_at FROM products WHERE status=1") as $p) $urls[] = SITE_URL . '/product/' . $p['slug'];
foreach ($pdo->query("SELECT slug FROM blogs WHERE status='published'") as $b) $urls[] = SITE_URL . '/blog/' . $b['slug'];
foreach ($pdo->query("SELECT slug FROM pages WHERE status=1") as $p) $urls[] = SITE_URL . '/page/' . $p['slug'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($urls as $u) echo '<url><loc>' . e($u) . '</loc></url>';
echo '</urlset>';
