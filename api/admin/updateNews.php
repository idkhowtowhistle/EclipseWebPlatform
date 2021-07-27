<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(!SESSION["adminLevel"] || SESSION["adminLevel"] < 2){api::respond(400, false, "Not an administrator!"); }
if(!isset($_POST["body"])) { api:: respond(400, false, "Invalid Request"); }
if(!isset($_POST["title"])) { api:: respond(400, false, "Invalid Request"); }
if(!trim($_POST["body"])) {api::respond(400, false, "Invalid Request");}
if(!trim($_POST["title"])) {api::respond(400, false, "Invalid Request");}
if(!htmlspecialchars($_POST["body"])) {api::respond(400, false, "Invalid Request");}
if(!htmlspecialchars($_POST["title"])) {api::respond(400, false, "Invalid Request");}
if(strlen($_POST["body"]) > 1000 || strlen($_POST["body"]) < 3) {api::respond(400, false, "You need to make the body less than 1000 characters or more than 3");}
if(strlen($_POST["title"]) > 50 || strlen($_POST["title"]) < 3) {api::respond(400, false, "You need to make the title less than 50 characters or more than 3");}
api::lastAction();

$body = $_POST["body"];
$creatorId = SESSION["userId"];
$id = 1;
$title = $_POST["title"];

$query = $pdo->prepare("UPDATE news SET news = :body, title = :title, date = UNIX_TIMESTAMP(), createdBy = :creatorId WHERE id = :keyId");
$query->bindParam(":body", $body, PDO::PARAM_STR);
$query->bindParam(":title", $title, PDO::PARAM_STR);
$query->bindParam(":creatorId", $creatorId, PDO::PARAM_INT);
$query->bindParam(":keyId", $id, PDO::PARAM_INT);
$query->execute();

api::respond(200, true, "News posted!");