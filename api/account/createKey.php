<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!isset($_POST["agree"])){api::respond(400, false, "Invalid Request");}
api::lastAction();

$terms = $_POST["agree"] == "false" ? false : true;
if(!$terms) {api::respond(400, false, "You need to tick the checkbox to create a key!");}

$creatorId = SESSION["userId"];

$query = $pdo->prepare("SELECT * FROM users WHERE id = :uid");
$query->bindParam(":uid", $creatorId, PDO::PARAM_INT);
$query->execute();
$row = $query->fetch(PDO::FETCH_OBJ);

if(SESSION["ssc"]) {
	api::respond(400, false, "You cannot create an invite key.");
}

if($row->createdInv+5259600 * 2 > time())
{
	api::respond(400, false, "You can create a key after ".ceil((($row->createdInv+5259600 * 2)-time())/172800)." days.");
}

$inviteKey = invite::generateKey();

$query = $pdo->prepare("UPDATE users SET createdInv = UNIX_TIMESTAMP() WHERE id = :uid");
$query->bindParam(":uid", $creatorId, PDO::PARAM_INT);
$query->execute();


$query = $pdo->prepare("INSERT INTO invitekeys (inviteKey, creatorId, createdOn) VALUES (:inviteKey, :creatorId, UNIX_TIMESTAMP())");
$query->bindParam(":inviteKey", $inviteKey, PDO::PARAM_STR);
$query->bindParam(":creatorId", $creatorId, PDO::PARAM_INT);
$query->execute();

admin::updateAuditLogs($creatorId, 1, "Created invite key", SESSION["userName"] . " created invite key " . $inviteKey);
api::respond(200, true, "Use your key wisely...");