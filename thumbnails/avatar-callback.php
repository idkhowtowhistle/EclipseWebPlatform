<?php
/**
 * Render callback.
 *
 * RCCService POSTs here three times during a render:
 *   1. "Rendering"                 (the ping sent by Render/init.luau)
 *   2. "Completed\n<body>\n<head>" (bodyshot 840x840 and headshot 300x300)
 *   3. "Error\n<message>"          (when the render script raised)
 *
 * Both images are base64 PNGs with a transparent background.
 */
$root = dirname(__DIR__);

$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'POST';
$_SERVER['REQUEST_URI'] = '/thumbnails/avatar-callback.php';
$_SERVER['HTTP_USER_AGENT'] = 'RCCService';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['SCRIPT_NAME'] = '/thumbnails/avatar-callback.php';

require $root . '/api/private/core.php';
require $root . '/api/private/avatar.php';

ini_set('display_errors', '0');

$userId = (int)($_GET['id'] ?? 0);
$version = preg_replace('/[^a-f0-9]/', '', (string)($_GET['v'] ?? ''));
$token = (string)($_GET['token'] ?? '');

if ($userId <= 0 || $version === '' || $token === '') {
    http_response_code(400);
    echo 'missing parameters';
    exit;
}

if (!hash_equals(avatarRenderer::token($userId, $version), $token)) {
    avatarRenderer::log('callback ' . $userId . ': invalid token');
    http_response_code(403);
    echo 'invalid token';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'POST') !== 'POST') {
    http_response_code(405);
    echo 'method not allowed';
    exit;
}

$raw = file_get_contents('php://input');
if ($raw === false || $raw === '') {
    avatarRenderer::log('callback ' . $userId . ': empty body');
    avatarRenderer::unmark($userId);
    http_response_code(400);
    echo 'empty body';
    exit;
}

if (substr($raw, 0, 2) === "\x1f\x8b") {
    $decoded = @gzdecode($raw);
    if ($decoded !== false) {
        $raw = $decoded;
    }
}

$lines = explode("\n", $raw);
$status = trim((string)array_shift($lines));

// The rendering ping is not a result.
if (strcasecmp($status, 'Rendering') === 0) {
    http_response_code(200);
    echo 'ok';
    exit;
}

if ($status !== 'Completed') {
    avatarRenderer::log(
        'callback ' . $userId . ': render status ' . substr($status, 0, 20)
        . ' ' . substr(trim(implode("\n", $lines)), 0, 300)
    );
    avatarRenderer::unmark($userId);
    http_response_code(422);
    echo 'render failed';
    exit;
}

$decode = function ($payload) {
    $payload = preg_replace('/\s+/', '', (string)$payload);
    if ($payload === '') {
        return false;
    }
    $png = base64_decode($payload, true);
    if ($png === false || substr($png, 0, 8) !== "\x89PNG\r\n\x1a\n") {
        return false;
    }
    return $png;
};

$bodyPng = $decode($lines[0] ?? '');
if ($bodyPng === false) {
    avatarRenderer::log('callback ' . $userId . ': invalid bodyshot png');
    avatarRenderer::unmark($userId);
    http_response_code(422);
    echo 'invalid image';
    exit;
}

$headPng = $decode($lines[1] ?? '');

// If the user changed their appearance while this render was running,
// discard it. Do not remove the marker: a newer render may own it.
$current = avatarRenderer::get($userId);
if (avatarRenderer::version($current) !== $version) {
    avatarRenderer::log(
        'callback ' . $userId . ': stale version ' . $version
        . ' (current ' . avatarRenderer::version($current) . ')'
    );
    http_response_code(409);
    echo 'stale';
    exit;
}

if (!avatarRenderer::store($userId, $bodyPng, $version)) {
    avatarRenderer::unmark($userId);
    http_response_code(500);
    echo 'store failed';
    exit;
}

if ($headPng !== false) {
    avatarRenderer::storeHeadshot($userId, $headPng);
}

avatarRenderer::unmark($userId);
avatarRenderer::log('callback ' . $userId . ': stored version ' . $version);

http_response_code(200);
echo 'ok';
