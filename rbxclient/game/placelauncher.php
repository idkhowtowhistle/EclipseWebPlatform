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

$loggedIn = SESSION != false;
$userId = SESSION["userId"] ?? 0;
$isAdmin = isset(SESSION["adminLevel"]) && SESSION["adminLevel"] >= 2;
$ticket = generateUUID();

// implement gross hack for android app launching
$isMobile = !is_numeric($serverId);

if ($request != "RequestGame") die(json_encode(["error" => "Invalid request type"]));
if (!strlen($serverId)) respond(["status" => 11, "message" => "Cannot join game wihtout placeId."]);

if ($isMobile)
{
	$ticketInfo = games::getClientInfo($serverId);
	if($ticketInfo && $ticketInfo->valid)
	{
		$loggedIn = true;
		$ticket = $serverId;
		$userId = $ticketInfo->userId;
		$serverId = $ticketInfo->serverId;
	}
}

if (!$loggedIn) respond(["status" => 11, "message" => "Cannot join game with no authenticated user."]);

$gameInfo = games::getGameInfoFromId($serverId);

if (!$gameInfo) respond(["status" => 4, "message" => "Unknown Error"]);

if(SESSION["adminLevel"] < 2) 
{
    if(games::getPlayerCountInServer($serverId) >= $gameInfo->serverSize)
    	respond(["status" => 6, "message" => "The game you requested is currently full."]);

    if($gameInfo->isPrivate && $privateLink != $gameInfo->privateKey) 
    	respond(["status" => 11, "message" => "You are not allowed to play this game."]);
}

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