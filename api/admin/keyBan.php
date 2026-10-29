<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!SESSION) { api::respond(405, false, "Method Not Allowed"); }
// if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(!SESSION["adminLevel"] || SESSION["adminLevel"] < 2){api::respond(400, false, "Not an administrator!"); }
if(!isset($_POST["username"]) || !isset($_POST["reason"]) || !isset($_POST["option"])) api::respond(400, false, "Invalid Request");
if(!trim($_POST["username"]) || !trim($_POST["reason"])) {api::respond(400, false, "You have empty fields");}
if($_POST["option"] < 0 || $_POST["option"] > 1) api::respond(400, false, "Invalid Request");
if(!is_numeric($_POST["option"])) {api::respond(400, false, "Invalid Request");}
api::lastAction();

$option = $_POST["option"];
$username = $_POST["username"];
$modId = SESSION["userId"];
$reason = $_POST["reason"];

$userInfo = users::getUserInfoFromUserName($username);

if(!$userInfo) api::respond(400, false, "This user does not exist");

$userId = $userInfo->id;

if($userInfo->id == $modId) api::respond(400, false, "You cannot key ban yourself!");
if($userInfo->admin > 2) api::respond(400, false, "You cannot key ban staff members!");

if(!$userInfo->canCreateKeys && $option === 2) api::respond(400, false, "This user already has been game banned.");
if(strlen($reason) > 512 || strlen($reason) < 3) api::respond(400, false, "The reason must be 3-512 characters.");

$query = $pdo->prepare("UPDATE users SET canCreateKeys = :option WHERE id = :uid");
$query->bindParam(":option", $option, PDO::PARAM_INT);
$query->bindParam(":uid", $userId, PDO::PARAM_INT);
$query->execute();

$text = 
[ 
	0 => "keybanned", 
	1 => "unbanned"
];

admin::updateAuditLogs($userId, 2, "Moderate", $username." has been ".$text[$option]. " for " . $reason);
api::respond(200, true, $userInfo->username." has been ".$text[$option]);