<?php
/**
 * CLI worker: builds the avatar Lua and submits it to the local RCCService.
 * Started in the background by /thumbnails/avatar.php.
 */
$root = dirname(__DIR__);

$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/thumbnails/render-worker.php';
$_SERVER['HTTP_USER_AGENT'] = 'MercuryRenderWorker/1.0';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['SCRIPT_NAME'] = '/thumbnails/render-worker.php';

require $root . '/api/private/core.php';
require $root . '/api/private/avatar.php';

ini_set('display_errors', '0');

$userId = (int)($argv[1] ?? 0);
if ($userId <= 0) {
    exit(1);
}

$lockPath = avatarRenderer::dir() . '/.worker-' . $userId . '.lock';
$lock = @fopen($lockPath, 'c');
if ($lock && !flock($lock, LOCK_EX | LOCK_NB)) {
    avatarRenderer::log('worker ' . $userId . ' already running, exiting');
    exit(0);
}

$submitted = false;
register_shutdown_function(function () use ($userId, &$submitted) {
    if (!$submitted) {
        avatarRenderer::unmark($userId);
    }
});

avatarRenderer::log('worker start for user ' . $userId);

try {
    $exists = db::run(
        "SELECT id FROM users WHERE id = :id",
        [":id" => $userId]
    )->fetchColumn();
} catch (Exception $e) {
    avatarRenderer::log('worker ' . $userId . ' db error: ' . $e->getMessage());
    exit(2);
}

if (!$exists) {
    avatarRenderer::log('worker ' . $userId . ': user does not exist');
    exit(2);
}

$appearance = avatarRenderer::get($userId);
$appearance['userId'] = $userId;
$version = avatarRenderer::version($appearance);
$callback = avatarRenderer::callbackUrl($userId, $version);
$lua = avatarRenderer::lua($appearance, $callback);

avatarRenderer::log(
    'worker ' . $userId . ' appearance ' . json_encode($appearance) . ' version ' . $version
);

if (!avatarRenderer::ensureRcc()) {
    exit(3);
}

$jobId = 'avatar_' . $userId . '_' . time() . '_' . mt_rand(1000, 9999);
if (!avatarRenderer::submit($lua, $jobId)) {
    exit(4);
}

$submitted = true;
avatarRenderer::log('worker ' . $userId . ' submitted ' . $jobId);

if ($lock) {
    flock($lock, LOCK_UN);
    fclose($lock);
}

exit(0);
