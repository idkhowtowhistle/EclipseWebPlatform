<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/private/core.php';
header("content-type: application/json");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    api::respond(405, false, "Method Not Allowed");
}
api::requireLogin();
if (SESSION["adminLevel"] > 1 && !SESSION["2fa"]) api::respond(400, false, "You need 2FA enabled to do any administrator action");
if (!isset($_POST["gameId"]) || !isset($_POST["serverName"])) api::respond(400, false, "Invalid Request");
if (!trim($_POST["serverName"])) api::respond(400, false, "You need to enter your server name to delete the server");

$gameId = $_POST["gameId"];
$gameInfo = games::getGameInfoFromId($gameId);
$serverName = $_POST["serverName"];

if (!$gameInfo || $gameInfo->isDeleted) api::respond(400, false, "This server does not exist");
if (SESSION["adminLevel"] < 2) {
    if (SESSION["userId"] != $gameInfo->ownerId) api::respond(400, false, "You do not own this game!");
}
if($gameInfo->serverName != $serverName) api::respond(400, false, "Incorrect server name");

api::lastAction();

$query = $pdo->prepare("UPDATE games SET isDeleted = 1 WHERE id = :id");
$query->bindParam(":id", $gameId, PDO::PARAM_INT);
$query->execute();

admin::updateAuditLogs($creatorId, 2, "Games", "Deleted server ".$gameId);
api::respond(200, true, "Deleted server");