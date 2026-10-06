<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli-server') {
    require __DIR__ . '/index.php';
    return;
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$requestPath = '/' . ltrim($requestPath, '/');

$blockedPath = preg_match(
    '#^/(?:attached_assets|includes|admin/includes|\.agents|\.local|\.git|\.cache|var)(?:/|$)#i',
    $requestPath
);
$blockedFile = preg_match(
    '#^/(?:database\.sql|README-INSTALLATION\.txt|zipFile\.zip|php-router\.php|\.replit|\.gitignore|pyproject\.toml|uv\.lock|main\.py)$#i',
    $requestPath
) || preg_match('#\.(?:sqlite|db|sql|log|pyc)$#i', $requestPath);

if ($blockedPath || $blockedFile || str_contains($requestPath, "\0")
    || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $requestPath)) {
    http_response_code(404);
    exit('Not found.');
}

$routes = [
    '#^/category/([a-z0-9-]+)/?$#i' => ['category.php', 'slug'],
    '#^/product/([a-z0-9-]+)/?$#i' => ['product.php', 'slug'],
    '#^/blog/([a-z0-9-]+)/?$#i' => ['article.php', 'slug'],
    '#^/page/([a-z0-9-]+)/?$#i' => ['page.php', 'slug'],
];
foreach ($routes as $pattern => [$script, $parameter]) {
    if (preg_match($pattern, $requestPath, $matches)) {
        $_GET[$parameter] = $matches[1];
        require __DIR__ . '/' . $script;
        return;
    }
}

$relativePath = ltrim($requestPath, '/');
$targetPath = __DIR__ . ($relativePath === '' ? '/index.php' : '/' . $relativePath);
$realRoot = realpath(__DIR__);
$realTarget = realpath($targetPath);
if ($realRoot !== false && $realTarget !== false
    && ($realTarget === $realRoot || str_starts_with($realTarget, $realRoot . DIRECTORY_SEPARATOR))) {
    return false;
}

http_response_code(404);
require __DIR__ . '/404.php';
