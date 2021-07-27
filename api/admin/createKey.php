<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!isset($_POST["agree"])){api::respond(400, false, "Invalid Request");}
if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(!SESSION["adminLevel"] || SESSION["adminLevel"] < 2){api::respond(400, false, "Not an administrator!"); }
api::lastAction();

$terms = $_POST["agree"] == "false" ? false : true;
if(!$terms) {api::respond(400, false, "You need to tick the checkbox to create a key!");}

$inviteKey = invite::generateKey();
$creatorId = SESSION["userId"];


$query = $pdo->prepare("INSERT INTO invitekeys (inviteKey, creatorId, createdOn) VALUES (:inviteKey, :creatorId, UNIX_TIMESTAMP())");
$query->bindParam(":inviteKey", $inviteKey, PDO::PARAM_STR);
$query->bindParam(":creatorId", $creatorId, PDO::PARAM_INT);
$query->execute();

api::respond(200, true, "Invite key has been created.");