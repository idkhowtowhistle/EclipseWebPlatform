<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(!SESSION["adminLevel"] || SESSION["adminLevel"] < 2){api::respond(400, false, "Not an administrator!"); }
if(!isset($_POST["keyId"])) { api:: respond(400, false, "Invalid Request"); }
if(!trim($_POST["keyId"])) {api::respond(400, false, "Invalid Request");}
if(!is_numeric($_POST["keyId"])) {api::respond(400, false, "Invalid Request");}

api::lastAction();

$keyId = $_POST["keyId"];

$key = invite::getKeyInfo($keyId);

$delete = true;

if(!$key){api::respond(400, false, "This key does not exist");}
if($key->deleted){api::respond(400, false, "This key does not exist");}

$query = $pdo->prepare("UPDATE invitekeys SET deleted = :deleted WHERE id = :keyId");
$query->bindParam(":deleted", $delete, PDO::PARAM_INT);
$query->bindParam(":keyId", $keyId, PDO::PARAM_INT);
$query->execute();

api::respond(200, true, "Invite Key $keyId has been deleted.");