<?php
require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";
general::checkApiKey("DiscordBot");

header("content-type: application/json");
if($_SERVER['REQUEST_METHOD'] != 'GET') api::respond(405, false, "Method Not Allowed");

if (isset($_GET["Token"]))
{

}
else if (isset($_GET["DiscordID"]))
{
	$info = db::run("SELECT userId FROM users WHERE discordId = :id", [":id" => $_GET["DiscordID"]]);
	if (!$info->rowCount()) api::respond(200, false, "Not Verified");
	$username = users::getUserNameFromUid($info->fetchColumn());
	api::respond(200, true, $username);
}

api::respond(200, false, "Bad Request");