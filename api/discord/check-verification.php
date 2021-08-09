<?php
require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";
general::checkApiKey("DiscordBot");

header("content-type: application/json");
if($_SERVER['REQUEST_METHOD'] != 'GET') api::respond(405, false, "Method Not Allowed");

if (isset($_GET["Token"]) && isset($_GET["DiscordID"]))
{
	$userInfo = db::run("SELECT * FROM users WHERE discordKey = :key", [":key" => $_GET["Token"]])->fetch(PDO::FETCH_OBJ);
	if (!$userInfo->rowCount()) api::respond(200, false, "InvalidKey"); // check if verification key is valid
	if ($userInfo->discordId != NULL) api::respond(200, false, "AlreadyVerified"); // check if mercury account is already verified

	/* db::run(
		"UPDATE users SET discordId = :id WHERE discordKey = :key", 
		[":id" => $_GET["DiscordID"], ":key" => $_GET["Token"]]
	); */

	api::respond(200, true, $userInfo->username);
}
else if (isset($_GET["DiscordID"]))
{
	$username = db::run("SELECT username FROM users WHERE discordId = :id", [":id" => $_GET["DiscordID"]]);
	if (!$username->rowCount()) api::respond(200, false, "NotVerified"); // check if discord account is already verified
	api::respond(200, true, $username->fetchColumn());
}

api::respond(200, false, "Bad Request");