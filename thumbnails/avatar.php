<?php
require __DIR__ . '/../api/private/avatar.php';

$id  = (int)($_GET['id'] ?? $_GET['userId'] ?? 0);
$x   = (int)($_GET['x'] ?? 100);
$y   = (int)($_GET['y'] ?? 100);

if ($x === 200 && $y === 200) {
    $x = 250;
    $y = 250;
}
if ($x !== $y || !in_array($x, avatarRenderer::SIZES, true)) {
    $x = 100;
    $y = 100;
}

$dir    = avatarRenderer::dir();
$name   = avatarRenderer::thumbName($id);
$file   = $dir . '/' . $name . '-' . $x . 'x' . $y . '.png';
$sample = $dir . '/sample-' . $x . 'x' . $y . '.png';

// Thumbnails rendered before the switch to username file names.
if (!is_file($file) && $name !== (string)$id) {
    $legacy = $dir . '/' . $id . '-' . $x . 'x' . $y . '.png';
    if (is_file($legacy)) {
        $file = $legacy;
    }
}

header('Content-Type: image/png');

// Rendered avatar available: serve it and let the browser cache it. Pages that
// pass ?v= (after a re-render) get the long cache; plain URLs stay short so a
// colour change shows up quickly.
if ($id > 0 && is_file($file) && filesize($file) > 0) {
    header(
        'Cache-Control: public, max-age='
        . (isset($_GET['v']) ? '2628000' : '300')
    );
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

// Not rendered yet: kick off a background render and show a placeholder.
if ($id > 0) {
    avatarRenderer::spawn($id);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
} else {
    header('Cache-Control: public, max-age=2628000');
}

if (is_file($sample) && filesize($sample) > 0) {
    header('Content-Length: ' . filesize($sample));
    readfile($sample);
    exit;
}

// Last-resort placeholder (1x1 transparent PNG).
echo base64_decode(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk'
    . 'YAAAAAYAAjCB0C8AAAAASUVORK5CYII='
);
