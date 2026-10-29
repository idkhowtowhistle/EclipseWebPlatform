<?php
/**
 * Runs a raw Lua script on the local RCCService.
 *   php api/private/job.php script.lua [jobId]
 * Script output is printed by RCCService in its console; anything the script
 * needs to report should be POSTed to /thumbnails/avatar-callback.php.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/avatar.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$scriptPath = $argv[1] ?? '';
if ($scriptPath === '' || !is_file($scriptPath)) {
    fwrite(STDERR, "usage: php job.php <script.lua> [jobId]\n");
    exit(1);
}

$lua = file_get_contents($scriptPath);
$jobId = $argv[2] ?? ('manual_' . time());
$ok = avatarRenderer::submit($lua, $jobId);

echo $ok ? "submitted\n" : "failed\n";
exit($ok ? 0 : 1);
