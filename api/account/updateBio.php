<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/private/core.php';
header("content-type: application/json");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    api::respond(405, false, "Method Not Allowed");
}
api::requireLogin();
if (!isset($_POST['bio'])) {
    api::respond(400, false, "Invalid Request");
}
if (!isset($_POST["message"])) {
    api::respond(400, false, "Invalid Request");
}
if (!isset($_POST["theme"])) {
    api::respond(400, false, "Invalid Request");
}

function swearFilter($text)
{
    $swearList = ["shit", "fuck", "nigg", "nigga", "Nig", "NIg", "NIG", "nig", "tranny", "faggot"];
    if (str_ireplace($swearList, "", $text) != $text) return true;
}


$bio = htmlspecialchars(trim($_POST["bio"]));
$message = $_POST["message"] == "false" ? false : true;
$theme = $_POST["theme"];

if($theme < 0 || $theme > 2 || !is_numeric($theme)) api::respond(400, false, "Invalid Request");

api::lastAction();

$userid = SESSION["userId"];

if (!trim($_POST['bio'])) {
    api::respond(400, false, "Your blurb can't be empty!");
}
if (strlen($_POST['bio']) > 250) {
    api::respond(400, false, "Your blurb is too large!");
}

if (swearFilter($bio)) {
    api::respond(400, false, "You can't have an inappropriate blurb");
}

$query = $pdo->prepare("UPDATE users SET blurb = :bio, messages = :messages, theme = :theme WHERE id = :uid");
$query->bindParam(":messages", $message, PDO::PARAM_INT);
$query->bindParam(":uid", $userid, PDO::PARAM_INT);
$query->bindParam(":theme", $theme, PDO::PARAM_INT);
$query->bindParam(":bio", $bio, PDO::PARAM_STR);
$query->execute();
    
api::respond(200, true, "Settings updated. $theme");
