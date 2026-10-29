<?php
/**
 * Serves a user's headshot (the 300x300 render saved next to the body
 * thumbnails by the render callback).
 *
 *   /thumbnails/headshot?id=1&x=250&y=250
 *
 * Falls back to the short {name}-head.png (150x150) when the requested size has
 * not been generated yet, and to the full-body thumbnail when there is no
 * headshot at all.
 */
require __DIR__ . '/../api/private/avatar.php';

$id = (int)($_GET['id'] ?? $_GET['userId'] ?? 0);
$x  = (int)($_GET['x'] ?? 250);
$y  = (int)($_GET['y'] ?? 250);

if ($x !== $y || !in_array($x, avatarRenderer::HEAD_SIZES, true)) {
    $x = 250;
    $y = 250;
}

$dir  = avatarRenderer::dir();
$name = avatarRenderer::thumbName($id);

$file   = $dir . '/' . avatarRenderer::headFile($name, $x);
$short  = $dir . '/' . avatarRenderer::headFile($name, 150);
$legacy = $dir . '/' . $id . '-head.png';

header('Content-Type: image/png');

$cache = 'public, max-age=' . (isset($_GET['v']) ? '2628000' : '300');

if ($id > 0) {
    foreach ([$file, $short, $legacy] as $candidate) {
        if (is_file($candidate) && filesize($candidate) > 0) {
            header('Cache-Control: ' . $cache);
            header('Content-Length: ' . filesize($candidate));
            readfile($candidate);
            exit;
        }
    }

    // No headshot yet: queue a render and fall back to the body thumbnail.
    avatarRenderer::spawn($id);
    header('Cache-Control: no-cache, no-store, must-revalidate');

    $body = $dir . '/' . $name . '-250x250.png';
    if (is_file($body) && filesize($body) > 0) {
        header('Content-Length: ' . filesize($body));
        readfile($body);
        exit;
    }
}

// Last-resort placeholder (1x1 transparent PNG).
echo base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk'
    . 'YAAAAAYAAjCB0C8AAAAASUVORK5CYII='
);
