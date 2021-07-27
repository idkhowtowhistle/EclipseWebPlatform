<?php 
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(SESSION["adminLevel"] < 2){api::respond(400, false, "Not an administrator");}
if(!isset($_POST['bio']) || !isset($_POST["userId"])){ api::respond(400, false, "Invalid Request"); }

$bio = htmlspecialchars(trim($_POST["bio"]));
$userid = htmlspecialchars(trim($_POST["userId"]));

$userInfo = users::getUserInfoFromUid($userid);

if($userInfo->admin > 1){api::respond(400, false, "You can't edit an admin's bio!");}

function swearFilter($text)
{
$swearList = ["shit", "fuck", "nigg", "nigga", "Nig", "NIg", "NIG", "nig", "tranny", "faggot"];
if (str_ireplace($swearList, "", $text) != $text) return true;
}

api::lastAction();

if(!strlen($_POST['bio'])){ api::respond(400, false, "Blurb can't be empty!"); }
if(strlen($_POST['bio']) > 250){ api::respond(400, false, "Blurb is too large!"); }

if(swearFilter($bio)) {
    api::respond(400, false, "You can't have an inappropriate blurb");
}

$query = $pdo->prepare("UPDATE users SET blurb = :bio WHERE id = :uid");
$query->bindParam(":uid", $userid, PDO::PARAM_INT);
$query->bindParam(":bio", $bio, PDO::PARAM_STR);

admin::updateAuditLogs($_POST["userId"], 1, "Edit User Bio", "Edited $userInfo->username's bio to $bio");

if($query->execute()){ api::respond(200, true, "User bio updated."); }
else{ api::respond(500, false, "Internal Server Error"); }