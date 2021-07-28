<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST') api::respond(405, false, "Method Not Allowed"); 
api::requireLogin();
if(SESSION["adminLevel"] < 2) api::respond(400, false, "You are not an administrator!");
if(!SESSION["2fa"]) api::respond(400, false, "You need 2fa to do any admin action");
if(!isset($_POST["postId"]) || !isset($_POST["locked"])) api::respond(400, false, "Invalid Request");
api::lastAction();

$userId = SESSION["userId"];
$postId = $_POST["postId"];
$locked = $_POST["locked"] == "false" ? false : true;

$postInfo = forum::getThreadInfo($postId);
if(!$postInfo) api::respond(400, false, "This post doesn't exist");

if($postInfo->locked && $locked || !$postInfo->locked && !$locked) api::respond(400, false, "This post is already locked");
