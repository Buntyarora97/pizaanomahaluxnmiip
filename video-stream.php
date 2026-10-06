<?php
/**
 * Range-enabled stream for the supplied homepage film.
 *
 * PHP's built-in preview server serves static files without a usable
 * seekable range in some browsers. Scrubbing needs byte-range responses.
 */
$file = __DIR__ . '/assets/uploads/mth/video/mth-trade-film.mp4';

if (!is_file($file) || !is_readable($file)) {
    http_response_code(404);
    exit('Video not found');
}

$size = (int) filesize($file);
$start = 0;
$end = $size - 1;
$status = 200;

header('Content-Type: video/mp4');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=3600');

$range = $_SERVER['HTTP_RANGE'] ?? '';
if ($range && preg_match('/bytes=(\d*)-(\d*)/', $range, $matches)) {
    if ($matches[1] === '' && $matches[2] === '') {
        http_response_code(416);
        header("Content-Range: bytes */{$size}");
        exit;
    }

    if ($matches[1] === '') {
        $suffixLength = min((int) $matches[2], $size);
        $start = $size - $suffixLength;
    } else {
        $start = (int) $matches[1];
    }

    if ($matches[2] !== '') {
        $end = min((int) $matches[2], $size - 1);
    }

    if ($start > $end || $start >= $size) {
        http_response_code(416);
        header("Content-Range: bytes */{$size}");
        exit;
    }

    $status = 206;
    http_response_code($status);
    header("Content-Range: bytes {$start}-{$end}/{$size}");
}

$length = $end - $start + 1;
header("Content-Length: {$length}");

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
    exit;
}

$handle = fopen($file, 'rb');
if (!$handle) {
    http_response_code(500);
    exit('Unable to open video');
}

fseek($handle, $start);
while ($length > 0 && !feof($handle)) {
    $chunk = fread($handle, min(8192, $length));
    if ($chunk === false || $chunk === '') {
        break;
    }
    echo $chunk;
    $length -= strlen($chunk);
    flush();
}
fclose($handle);