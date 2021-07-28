<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(SESSION["adminLevel"] < 2){ api::respond(400, false, "Not an administrator"); }
if(!isset($_POST["username"]) || !isset($_POST["type"]) || !isset($_POST["reason"]) || !isset($_POST["date"])){ api::respond(400, false, "Invalid Request"); }
if($_POST["type"] < 1 || $_POST["type"] > 4){ api::respond(400, false, "Invalid Request"); }
if($_POST["type"] != 4 && !trim($_POST["reason"])){ api::respond(400, false, "You must supply a reason"); }
if(!trim($_POST["username"])){ api::respond(400, false, "You haven't set the username to ban"); }
if($_POST["type"] == 2 && !trim($_POST["date"])){ api::respond(400, false, "Ban time not set"); }
if(strlen($_POST["reason"]) > 512 || strlen($_POST["reason"]) < 3  && $_POST["type"] != 4) {api::respond(400, false, "Reason must be more than 3 characters and less than 512");}



$type = htmlspecialchars($_POST["type"]);
$reason = trim($_POST["reason"]);
$userId = SESSION["userId"];
$bannedInfo = users::getUserInfoFromUserName(htmlspecialchars($_POST["username"]));

if(!$bannedInfo) api::respond(400, false, "This user doesn't exist!"); 

$date = strtotime($_POST["date"]. " " . date("G:i:s"));
$bannedId = $bannedInfo->id;



if($type == 4)
{
	if(!users::getUserModeration($bannedInfo->id)){ api::respond(400, false, "That user isn't banned!"); }
	$reason = "No reason specified";
	users::undoUserModeration($bannedInfo->id, true);
}
else
{
    if($bannedId == $userId){ api::respond(400, false, "You cannot moderate yourself!"); }
	if($bannedInfo->admin > 1){ api::respond(400, false, "You cannot moderate a staff member"); }
    if(users::getUserModeration($bannedId)){ api::respond(400, false, "That user is already banned!"); }
    if($type == 2 && $date < strtotime('tomorrow')){ api::respond(400, false, "Ban time must be at least 1 day long"); }

    $query = $pdo->prepare("INSERT INTO bans (userId, bannerId, timeStarted, timeEnds, reason, type) VALUES (:bid, :uid, UNIX_TIMESTAMP(), :ends, :reason, :type)");
	$query->bindParam(":bid", $bannedId, PDO::PARAM_INT);
	$query->bindParam(":uid", $userId, PDO::PARAM_INT);
	$query->bindParam(":ends", $date, PDO::PARAM_INT);
	$query->bindParam(":reason", $reason, PDO::PARAM_STR);
	$query->bindParam(":type", $type, PDO::PARAM_INT);
	$query->execute();
}

$text = 
[ 
	1 => "warned", 
	2 => "banned for ".general::time_elapsed("@".($date+1), false, false), 
	3 => "permanently banned", 
	4 => "unbanned" 
];

admin::updateAuditLogs($bannedId, 2, "Moderate", $bannedInfo->username." has been ".$text[$type]. " for " . $reason);

api::respond(200, true, $bannedInfo->username." has been ".$text[$type]);