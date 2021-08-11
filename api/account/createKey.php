<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST') api::respond(405, false, "Method Not Allowed");
api::requireLogin();

if(!isset($_POST["agree"])) api::respond(400, false, "Bad Request");
api::lastAction();

$terms = $_POST["agree"] != "false";
if(!$terms) api::respond(200, false, "You need to tick the checkbox to create a key!");

$lastInvite = db::run("SELECT createdInv FROM users WHERE id = :uid", [":uid" => SESSION["userId"]])->fetchColumn();

if(SESSION["ssc"] || !SESSION["keys"]) 
{
	api::respond(200, false, "You cannot create an invite key.");
}

if(strtotime("2 weeks", SESSION["userInfo"]["joined"]) > time()) 
{
	api::respond(200, false, "Your account must be at least two weeks old!");
}

if(strtotime("2 months", $lastInvite) > time())
{
	api::respond(200, false, "You must wait ".ceil((strtotime("2 months", $lastInvite)-time())/strtotime("1 day", 0))." days before making a new key.");
}

$inviteKey = invite::generateKey();

db::run("UPDATE users SET createdInv = UNIX_TIMESTAMP() WHERE id = :uid", [":uid" => SESSION["userId"]]);
db::run("INSERT INTO invitekeys (inviteKey, creatorId, createdOn) VALUES (:inviteKey, :creatorId, UNIX_TIMESTAMP())", [":inviteKey" => $inviteKey, ":creatorId" => SESSION["userId"]]);

admin::updateAuditLogs($creatorId, 1, "Created invite key", SESSION["userName"] . " created invite key " . $inviteKey);
api::respond(200, true, "Use your key wisely...");