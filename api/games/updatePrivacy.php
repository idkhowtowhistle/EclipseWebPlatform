<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/private/core.php';
header("content-type: application/json");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    api::respond(405, false, "Method Not Allowed");
}
api::requireLogin();
if (SESSION["adminLevel"] > 1 && !SESSION["2fa"]) api::respond(400, false, "You need 2FA enabled to do any administrator action");
if (!isset($_POST["gameId"]) || !isset($_POST["privacy"])) api::respond(400, false, "Invalid Request");

$gameId = $_POST["gameId"];
$gameInfo = games::getGameInfoFromId($gameId);
$privacy = $_POST["privacy"] == "false" ? false : true;

if (!$gameInfo || $gameInfo->isDeleted) api::respond(400, false, "This server does not exist");
if (SESSION["adminLevel"] < 2) {
    if (SESSION["userId"] != $gameInfo->ownerId) api::respond(400, false, "You do not own this game!");
}

api::lastAction();

$query = $pdo->prepare("UPDATE games SET isPrivate = :privacy WHERE id = :id");
$query->bindParam(":privacy", $privacy, PDO::PARAM_INT);
$query->bindParam(":id", $gameId, PDO::PARAM_INT);
$query->execute();


api::respond(200, true, "Updated privacy settings");