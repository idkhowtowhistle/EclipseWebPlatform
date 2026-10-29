<?php
/**
 * Asset endpoint used by RCCService while it builds an avatar.
 *
 *   /asset/characterfetch/{userId}            appearance document
 *   /asset/characterfetch.ashx?userId={id}    same, legacy 2013 path
 *   /asset/avataraccoutrements.ashx?userId=   body colours, legacy path
 *   /asset?id={assetId}                       model/asset by id
 *
 * The characterfetch document is the 2013 character appearance file: body
 * colours as BrickColor indexes plus the classic smile face. Player:LoadCharacter
 * fetches it exactly as the Render pipeline expects.
 */
$root = __DIR__;

$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['SCRIPT_NAME'] = '/asset.php';

require $root . '/api/private/core.php';
require $root . '/api/private/avatar.php';

$type = (string)($_GET['type'] ?? '');
$id = (int)($_GET['id'] ?? $_GET['userId'] ?? 0);

if ($type === 'characterfetch' || $type === 'avataraccoutrements') {
    if ($id <= 0) {
        http_response_code(400);
        header('Content-Type: text/plain');
        echo 'missing id';
        exit;
    }

    header('Content-Type: text/xml');
    header('Cache-Control: no-store');
    echo avatarRenderer::characterFetchXml($id);
    exit;
}

if ($type === 'asset') {
    // Only the classic face lives on the site; RCCService addresses it as
    // rbxasset://textures/face.png, so nothing else is served here yet.
    http_response_code(404);
    header('Content-Type: text/plain');
    echo 'asset not found';
    exit;
}

http_response_code(404);
header('Content-Type: text/plain');
echo 'not found';
