<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST') api::respond(405, false, "Method Not Allowed"); 
api::requireLogin();
if(SESSION["adminLevel"] < 2) api::respond(400, false, "You are not an administrator!");
if(!SESSION["2fa"]) api::respond(400, false, "You need 2fa to do any admin action");
if(!isset($_POST["postId"])) api::respond(400, false, "Invalid Request");
api::lastAction();

$userId = SESSION["userId"];
$postId = $_POST["postId"];

$postInfo = forum::getReplyInfo($postId);
if(!$postInfo) api::respond(400, false, "This post doesn't exist $postId");

$query = $pdo->prepare("UPDATE subforum_replies SET deleted = 1 WHERE id = :id");
$query->bindParam(":id", $postId, PDO::PARAM_INT);
$query->execute();

admin::updateAuditLogs($userId, 1, "Forum", "Deleted post reply #" . $postId);
api::respond(200, true, "Post has been deleted.");