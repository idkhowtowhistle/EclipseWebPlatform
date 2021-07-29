<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/private/core.php';
header("content-type: application/json");

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    api::respond(405, false, "Method Not Allowed");
}
api::requireLogin();

if(SESSION["adminLevel"] > 1 && !SESSION["2fa"]) api::respond(400, false, "You need 2FA to do administrator actions");
if(!isset($_POST["serverName"]) || !isset($_POST["serverIp"]) || !isset($_POST["serverSize"]) || !isset($_POST["serverId"]) || !isset($_POST["serverDesc"]) || !isset($_POST["serverPort"])) api::respond(400, false, "Invalid Request");
if(!trim(htmlspecialchars($_POST["serverName"]))) api::respond(400, false, "You need to give your server a name");
if(!trim($_POST["serverIp"])) api::respond(400, false, "You need to give a server an IPV4 address");
if($_POST["serverSize"] < 1 && !is_numeric($_POST["serverSize"])) api::respond(400, false, "Invalid Request");
if(!trim(htmlspecialchars($_POST["serverDesc"]))) api::respond(400, false, "You need to give a description");
if(!trim($_POST["serverPort"])|| !is_numeric($_POST["serverPort"])) api::respond(400, false, "You need to give a server port");
if(strlen($_POST["serverName"]) > "256" || strlen($_POST["serverName"] < "3")) api::respond(400, false, "Server name must be between 3-256 characters");
if(strlen($_POST["serverDesc"]) > "512" || strlen($_POST["serverDesc"] < "3")) api::respond(400, false, "Server description must be between 3-512 characters");
if($_POST["sPort"] > 65535 || $_POST["sPort"] < 0){api::respond(400, false, "Your server port is invalid");}
if(!filter_var($_POST["serverIp"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) api::respond(400, false, "Invalid IP address, must be IPV4");
api::lastAction();

$serverName = $_POST["serverName"];
$serverIp = $_POST["serverIp"];
$serverLimit = $_POST["serverSize"];
$serverPort = $_POST["serverPort"];
$serverDesc = $_POST["serverDesc"];
$serverId = $_POST["serverId"];

if(SESSION["gameban"]) api::respond(400, false, "Your account has been gamebanned");
$gameInfo = games::getGameInfoFromId($serverId);

if (!$gameInfo || $gameInfo->isDeleted) api::respond(400, false, "This server does not exist");
if (SESSION["adminLevel"] < 2) {
    if (SESSION["userId"] != $gameInfo->ownerId) api::respond(400, false, "You do not own this game!");
}

$query = $pdo->prepare("UPDATE games SET serverName = :serverName, serverIp = :serverIp, serverSize = :serverSize, serverPort = :serverPort, serverDesc = :serverDesc, lastUpdated = UNIX_TIMESTAMP() WHERE id = :id");
$query->bindParam(":serverName", $serverName, PDO::PARAM_STR);
$query->bindParam(":serverIp", $serverIp, PDO::PARAM_STR);
$query->bindParam(":serverSize", $serverLimit, PDO::PARAM_INT);
$query->bindParam(":serverPort", $serverPort, PDO::PARAM_INT);
$query->bindParam(":serverDesc", $serverDesc, PDO::PARAM_STR);
$query->bindParam(":id", $serverId, PDO::PARAM_INT);
$query->execute();

api::respond(200, true, "Edited server settings");