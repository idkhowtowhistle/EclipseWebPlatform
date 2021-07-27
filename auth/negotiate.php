<?php 
require $_SERVER['DOCUMENT_ROOT']."/api/private/core.php";
if($_SERVER["HTTP_USER_AGENT"] != "Roblox/WinInet") pageBuilder::errorCode(400);

function error($msg){ http_response_code(403); die($msg); }
header("content-type: text/plain; charset=utf-8");
$ticket = $_GET["suggest"] ?? false;

if(!isset($_SERVER["HTTP_RBXAUTHENTICATIONNEGOTIATION"])) error("Missing custom Roblox header.");
if(empty($ticket)) error("Authentication ticket was not sent.");

if(str_starts_with($ticket, "DEBUG:"))
{
	$username = substr($ticket, 6);
	$userid = users::getUidFromUsername($username);
	if(!$userid) error("Authentication ticket was invalid.");
	if(SESSION) session::logout(SESSION["sessionKey"], false);
	session::create($userid, true);
	die();
}

error("Authentication ticket was invalid.");