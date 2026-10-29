<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST') api::respond(405, false, "Method Not Allowed"); 
api::requireLogin();
if(SESSION["adminLevel"] < 2) api::respond(400, false, "You are not an administrator!");
// if(!SESSION["2fa"]) api::respond(400, false, "You need 2fa to do any admin action");
if(!isset($_POST["postId"]) || !isset($_POST["locked"])) api::respond(400, false, "Invalid Request");
api::lastAction();

$userId = SESSION["userId"];
$postId = $_POST["postId"];
$locked = $_POST["locked"] == "false" ? true : false;

$postInfo = forum::getThreadInfo($postId);
if(!$postInfo) api::respond(400, false, "This post doesn't exist");

$query = $pdo->prepare("UPDATE subforum_threads SET locked = :locked WHERE id = :id");
$query->bindParam(":locked", $locked, PDO::PARAM_INT);
$query->bindParam(":id", $postId, PDO::PARAM_INT);
$query->execute();

$text = [
    true => "Locked",
    false => "Unlocked"
];


admin::updateAuditLogs($userId, 1, "Forum", $text[$locked] . " post #" . $postId);
api::respond(200, true, "Post lock status changed");