<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!isset($_POST["username"]) || !isset($_POST["title"]) || !isset($_POST["message"])) {api::respond(400, false, "Invalid Request");}
if(!trim($_POST["username"])) {api::respond(400, false, "You need to add a username to send the message to");}
if(!trim($_POST["title"])) {api::respond(400, false, "You need to set a title");}
if(!trim($_POST["message"])) {api::respond(400, false, "You need to set a message");}
if(strlen($_POST["title"]) < 3 || strlen($_POST["title"]) > 128) {api::respond(400, false, "The length of the title needs to be 3-128 characters long.");}
if(strlen($_POST["message"]) < 3 || strlen($_POST["message"]) > 1024) {api::respond(400, false, "The message has to be 3-1024 characters long");}
if($_POST["username"] === SESSION["userName"]){api::respond(400, false, "Are you that lonely?");}

$username = users::getUidFromUsername($_POST["username"]);
$senderId = users::getUidFromUsername(SESSION["userName"]);
$title = $_POST["title"];
$message = $_POST["message"];

api::lastAction();

$sendToInfo = users::getUserInfoFromUid($username);
if(!$sendToInfo){api::respond(400, false, "This user does not exist.");}

if(!$sendToInfo->messages || $sendToInfo->ssc){api::respond(400, false, "This user has disabled their messages.");}
if(!SESSION["messages"] || SESSION["ssc"]){api::respond(400, false, "Enable messaging in Settings.");}

$query = $pdo->prepare("INSERT INTO messages (title, messageContent, messageFrom, messageTo, messageDate) VALUES (:title, :messageContent, :messageFrom, :messageTo, UNIX_TIMESTAMP())");
$query->bindParam(":title", $title, PDO::PARAM_STR);
$query->bindParam(":messageContent", $message, PDO::PARAM_STR);
$query->bindParam(":messageFrom", $senderId, PDO::PARAM_INT);
$query->bindParam(":messageTo", $username, PDO::PARAM_INT);
$query->execute();

api::respond(200, true, "Sent a message to " . users::getUsernameFromUid($username));