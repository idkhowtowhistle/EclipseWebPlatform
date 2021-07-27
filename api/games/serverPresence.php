<?php
require $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("content-type: text/plain; charset=utf-8");


if(!isset($_GET["ticket"]) || $_SERVER["HTTP_USER_AGENT"] != "Roblox/WinInet") die();

$query = $pdo->prepare("UPDATE games SET serverPing = UNIX_TIMESTAMP() WHERE serverTicket = :ticket");
$query->bindParam(":ticket", $_GET["ticket"], PDO::PARAM_STR);
$query->execute();

die("OK");