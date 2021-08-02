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
	"joinScriptUrl" => null,
	"authenticationUrl" => null,
	"authenticationTicket" => null,
	"message" => null
];

if($serverId == 154) //if($_SERVER["REMOTE_ADDR"] == "86.21.64.222" || $_SERVER["REMOTE_ADDR"] == "94.6.93.152")
{
	$ticket = generateUUID();
	games::createClientSession($serverId, 27, $ticket);
	$joinscripturl = "http://www.banland.xyz/game/join.ashx?ticket=$ticket";
	respond([
		"jobId" => "$serverId", 
		"status" => 2, 
		"joinScriptUrl" => $joinscripturl, 
		"authenticationUrl" => "https://www.banland.xyz/Login/Negotiate.ashx", 
		"authenticationTicket" => "test"
	]);
	die();
}

if ($request != "RequestGame") die(json_encode(["error" => "Invalid request type"]));
if (!$serverId) respond(["status" => 11, "message" => "Cannot join game wihtout placeId."]);
if (!SESSION) respond(["status" => 11, "message" => "Cannot join game with no authenticated user."]);

$userId = SESSION["userId"];
$gameInfo = games::getGameInfoFromId($serverId);

if (!$gameInfo) respond(["status" => 4, "message" => "Unknown Error"]);

if(SESSION["adminLevel"] < 2) 
{
    if(games::getPlayerCountInServer($serverId) >= $gameInfo->serverSize)
    	respond(["status" => 6, "message" => "The game you requested is currently full."]);

    if($gameInfo->isPrivate && $privateLink != $gameInfo->privateKey) 
    	respond(["status" => 11, "message" => "You are not allowed to play this game."]);
}

$ticket = generateUUID();
games::createClientSession($serverId, $userId, $ticket);
$joinscripturl = "http://banland.xyz/Game/Join.ashx?ticket=$ticket";

if($gameInfo->isPrivate) $joinscripturl .= "&privateKey=$privateLink";

respond([
	"jobId" => "$serverId", 
	"status" => 2, 
	"joinScriptUrl" => $joinscripturl, 
	"authenticationUrl" => "https://banland.xyz/Login/Negotiate.ashx", 
	"authenticationTicket" => "test"
]);
