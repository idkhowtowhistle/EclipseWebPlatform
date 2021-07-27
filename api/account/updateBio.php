<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!isset($_POST['bio'])){ api::respond(400, false, "Invalid Request"); }
if(!isset($_POST["message"])) {api::respond(400, false, "Invalid Request");}
if(!SESSION) { api::respond(405, false, "Method Not Allowed"); }

$bio = htmlspecialchars(trim($_POST["bio"]));
$message = $_POST["message"] == "false" ? false : true;


function swearFilter($text)
{
$swearList = ["shit", "fuck", "nigg", "nigga", "Nig", "NIg", "NIG", "nig", "tranny", "faggot"];
if (str_ireplace($swearList, "", $text) != $text) return true;
}


api::lastAction();

$userid = SESSION["userId"];

if(!strlen($_POST['bio'])){ api::respond(400, false, "Your blurb can't be empty!"); }
if(strlen($_POST['bio']) > 250){ api::respond(400, false, "Your blurb is too large!"); }

if(swearFilter($bio)) {
    api::respond(400, false, "You can't have an inappropriate blurb");
}

$query = $pdo->prepare("UPDATE users SET blurb = :bio, messages = :messages WHERE id = :uid");
$query->bindParam(":messages", $message, PDO::PARAM_INT);
$query->bindParam(":uid", $userid, PDO::PARAM_INT);
$query->bindParam(":bio", $bio, PDO::PARAM_STR);

if($query->execute()){ api::respond(200, true, "Settings updated."); }
else{ api::respond(500, false, "Internal Server Error"); }