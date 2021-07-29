<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!isset($_POST["sName"]) || !isset($_POST["sIp"]) || !isset($_POST["sLimit"]) || !isset($_POST["private"]) || !isset($_POST["sDesc"]) || !isset($_POST["sPort"])) api::respond(400, false, "Invalid Request");
if(!trim(htmlspecialchars($_POST["sName"]))) api::respond(400, false, "You need to give your server a name");
if(!trim($_POST["sIp"])) api::respond(400, false, "You need to give a server an IPV4 address");
if($_POST["sLimit"] < 1 && !is_numeric($_POST["sLimit"])) api::respond(400, false, "Invalid Request");
if(!trim(htmlspecialchars($_POST["sDesc"]))) api::respond(400, false, "You need to give a description");
if(!trim($_POST["sPort"])|| !is_numeric($_POST["sPort"])) api::respond(400, false, "You need to give a server port");
if(strlen($_POST["sName"]) > "256" || strlen($_POST["sName"] < "3")) api::respond(400, false, "Server name must be between 3-256 characters");
if(strlen($_POST["sDesc"]) > "512" || strlen($_POST["sDesc"] < "3")) api::respond(400, false, "Server description must be between 3-512 characters");
if($_POST["sPort"] > 65535 || $_POST["sPort"] < 0){api::respond(400, false, "Your server port is invalid");}
if(!filter_var($_POST["sIp"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) api::respond(400, false, "Invalid IP address, must be IPV4");
api::lastAction();

$privacy = $_POST["private"] == "false" ? false : true;
$creatorId = SESSION["userId"];
$serverName = $_POST["sName"];
$serverIp = $_POST["sIp"];
$serverLimit = $_POST["sLimit"];
$serverPort = $_POST["sPort"];
$serverDesc = $_POST["sDesc"];
$privateKey = generateUUID();
$ticket = generateUUID();

if(SESSION["gameban"]) api::respond(400, false, "Your account has been gamebanned");

$query = $pdo->prepare("INSERT INTO games (serverName, serverDesc, serverIp, serverPort, serverSize, createdOn, lastUpdated, ownerId, isPrivate, privateKey, serverTicket) VALUES (:sName, :sDesc, :sIp, :sPort, :sLimit, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :oId, :isPrivate, :privateKey, :sTicket)");
$query->bindParam(":sName", $serverName, PDO::PARAM_STR);
$query->bindParam(":sDesc", $serverDesc, PDO::PARAM_STR);
$query->bindParam(":sIp", $serverIp, PDO::PARAM_STR);
$query->bindParam(":sPort", $serverPort, PDO::PARAM_INT);
$query->bindParam(":sLimit", $serverLimit, PDO::PARAM_INT);
$query->bindParam(":oId", $creatorId, PDO::PARAM_INT);
$query->bindParam(":isPrivate", $privacy, PDO::PARAM_INT);
$query->bindParam(":privateKey", $privateKey, PDO::PARAM_STR);
$query->bindParam(":sTicket", $ticket, PDO::PARAM_STR);
$query->execute();

admin::updateAuditLogs($creatorId, 2, "Games", "Created server ".$serverName);
api::respond(200, true, "Created a server! Remember to host it");
