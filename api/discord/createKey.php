<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");


if($_SERVER['REQUEST_METHOD'] != 'GET'){ api::respond(405, false, "Method Not Allowed"); }
if(!is_numeric($_GET["dId"])) api::respond(400, false, "Invalid Request");
api::lastAction();


$discordId = $_GET["dId"] ?? false;
if(!$discordId) api::respond(400, false, "Key Not Generated");

$generatedKey = generateUUID();

$query = $pdo->prepare("INSERT INTO discordKeys (discordKey, discordId, dateCreated) VALUES (:discordKey, :discordId, UNIX_TIMESTAMP())");
$query->bindParam(":discordKey", $generatedKey, PDO::PARAM_STR);
$query->bindParam(":discordId", $discordId, PDO::PARAM_INT);
$query->execute();

api::respond(200, true, $generatedKey);