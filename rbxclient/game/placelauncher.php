<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

function respond($params)
{
	global $response;
	foreach ($params as $key => $value) $response[$key] = $value;
	die(json_encode($response));
}

$request = $_GET["request"] ?? false;
$serverId = $_GET["placeId"] ?? false;
$privateLink = $_GET["privateLink"] ?? false;
$response =
[
	"jobId" => null,
	"status" => null,
	"ticket" => null,
	"clientVersion" => null,
	"joinScriptUrl" => null,
	"authenticationUrl" => null,
	"authenticationTicket" => null,
	"message" => null
];

if ($request != "RequestGame") die(json_encode(["error" => "Invalid request type"]));
if (!$serverId) respond(["status" => 11, "message" => "Cannot join game without a placeId."]);
if (!SESSION) respond(["status" => 11, "message" => "Cannot join game with no authenticated user."]);

$userId = SESSION["userId"];
$gameInfo = games::getGameInfoFromId($serverId);

if (!$gameInfo) respond(["status" => 4, "message" => "Unknown Error"]);

if (SESSION["adminLevel"] < 2)
{
	if (games::getPlayerCountInServer($serverId) >= $gameInfo->serverSize)
		respond(["status" => 6, "message" => "The game you requested is currently full."]);

	if ($gameInfo->isPrivate && $privateLink != $gameInfo->privateKey)
		respond(["status" => 11, "message" => "You are not allowed to play this game."]);
}

$ticket = generateUUID();
games::createClientSession($serverId, $userId, $ticket);

$base = "http://" . $_SERVER["HTTP_HOST"];
$joinscripturl = $base . "/game/join?ticket=" . urlencode($ticket);
if ($gameInfo->isPrivate && $privateLink && $privateLink !== "false") $joinscripturl .= "&privateKey=" . urlencode($privateLink);

respond([
	"jobId" => (string)$serverId,
	"status" => 2,
	"ticket" => $ticket,
	"clientVersion" => (int)SITE_CONFIG["games"]["clientVersion"],
	"joinScriptUrl" => $joinscripturl,
	"authenticationUrl" => $base . "/negotiate",
	"authenticationTicket" => "test"
]);
