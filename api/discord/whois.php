<?php
require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";
general::checkApiKey("DiscordBot");

header("content-type: application/json");
if($_SERVER['REQUEST_METHOD'] != 'GET') api::respond(405, false, "Method Not Allowed");

if (isset($_GET["UserName"]))
{
	$userInfo = db::run(
		"SELECT id, username, blurb, admin, joined, lastonline, discordId FROM users WHERE username = :name", 
		[":name" => $_GET["UserName"]]
	)->fetch(PDO::FETCH_OBJ);
	if (!$userInfo) api::respond(200, false, "DoesntExist");
}
else if (isset($_GET["DiscordID"]))
{
	$userInfo = db::run(
		"SELECT id, username, blurb, admin, joined, lastonline, discordId FROM users WHERE discordId = :id", 
		[":id" => $_GET["DiscordID"]]
	)->fetch(PDO::FETCH_OBJ);
	if (!$userInfo) api::respond(200, false, "NotVerified");
}
else
{
	api::respond(200, false, "Bad Request");
}

api::respond(200, true, $userInfo);