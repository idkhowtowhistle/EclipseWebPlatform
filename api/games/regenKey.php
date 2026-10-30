<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/private/core.php';
header("content-type: application/json");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    api::respond(405, false, "Method Not Allowed");
}
api::requireLogin();
if (SESSION["adminLevel"] > 1 && !SESSION["2fa"]) api::respond(400, false, "You need 2FA enabled to do any administrator action");
if (!isset($_POST["gameId"])) api::respond(400, false, "Invalid Request");

$gameId = $_POST["gameId"];
$gameInfo = games::getGameInfoFromId($gameId);
$privateKey = generateUUID();

if (!$gameInfo || $gameInfo->isDeleted) api::respond(400, false, "This server does not exist");
if (SESSION["adminLevel"] < 2) {
    if (SESSION["userId"] != $gameInfo->ownerId) api::respond(400, false, "You do not own this game!");
}

api::lastAction();

db::run("UPDATE games SET privateKey = :privateKey WHERE id = :id", [":privateKey" => $privateKey, ":id" => $gameId]);

api::respond(200, true, siteBaseUrl() . "/games/view?id=$gameId&privateKey=$privateKey");