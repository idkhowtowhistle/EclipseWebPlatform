<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();

$serverId = $_POST["serverId"];
$privateLink = $_POST["privateLink"];
$userId = SESSION["userId"];
$ticket = generateUUID();

$gameInfo = games::getGameInfoFromId($serverId);
if(!$gameInfo) api::respond(400, false, "Invalid Request");


if(SESSION["adminLevel"] < 2) {
    if(games::getPlayerCountInServer($serverId) >= $gameInfo->serverSize) api::respond(400, false, "Server full");
    if($gameInfo->isPrivate && $privateLink != $gameInfo->privateKey) api::respond(400, false, "Server is private");
}

games::createClientSession($serverId, $userId, $ticket);

$joinscripturl = "http://banland.xyz/Game/Join.ashx?ticket=$ticket";

if($gameInfo->isPrivate) {
    $joinscripturl .= "&privateKey=$privateLink";
}

die(json_encode(["status" => 200, "success" => true, "message" => "OK", "joinscripturl" => $joinscripturl]));