<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
upload::initialize();

api::requireLogin();

api::lastAction();

$userId = SESSION["userId"];
$file = $_FILES["file"] ?? false;
$gd = new \Verot\Upload\Upload($file);
$type = $_POST["type"] ?? false;
$filename = trim(htmlspecialchars($_POST["name"] ?? false));
$desc = trim(htmlspecialchars($_POST["desc"] ?? false));
$allowed = ["png", "jpg", "jpeg", "xml", "mp3", "ogg", "wav", "lua", "rbxl"];
$price = $_POST["price"] ?? false;
$typeName = strtolower(upload::$types[$type]);

if(!$filename) {api::respond(400, false, "You need to give your item a name.");}
if(!$desc) {api::respond(400, false, "You need to give your item a description.");}
if(!$type) {api::respond(400, false, "Invalid Request");}
if(!is_numeric($price)){api::respond(400, false, "Invalid Request");}
if(strlen($desc) > 500) {api::respond(400, false, "Your description is too long.");}
if(strlen($filename) > 128) {api::respond(400, false, "Your name is too long.");}
if(!$gd->uploaded) { api::respond(400, false, "" . $gd->error); }
$ext = pathinfo($file["name"], PATHINFO_EXTENSION);

if(!in_array($ext, $allowed)) { api::respond(400, false, "You can't upload this type of file."); }
if(filesize($file["tmp_name"]) > 52428800) {api::respond(400, false, "File size too large.");}
if(!in_array($ext, ["png", "jpg", "jpeg"]) && in_array($type, [1, 2, 11, 12, 13])) {api::respond(400, false, "Only .png, .jpg or .jpeg files are allowed.");}
if(!in_array($ext, ["mp3", "ogg", "wav"]) && in_array($type, [3])) {api::respond(400, false, "Only .mp3, .ogg or .wav files can be uploaded.");}

if(in_array($ext, ["png", "jpg", "jpeg"]) && in_array($type, [2, 11, 12]) && $gd->image_src_x != 585 && !$gd->image_src_y != 559) {api::respond(400, false, "The template must be 585x559");}

if(file_exists($_SERVER['DOCUMENT_ROOT']."/thumbs/$typeName/".$file["name"])) {api::respond(400, false, "This file already exists.");}


$query = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE name = :name");
$query->bindParam(":name", $filename, PDO::PARAM_STR);
$query->execute();
if($query->fetchColumn()) {
    api::respond(400, false, "The name you have chosen has been taken.");
}


$query = $pdo->prepare("INSERT INTO assets (name, blurb, price, type, creatorId, createdOn, updatedOn) VALUES (:name, :blurb, :price, :type, :creatorId, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());");
$query->bindParam(":name", $filename, PDO::PARAM_STR);
$query->bindParam(":blurb", $desc, PDO::PARAM_STR);
$query->bindParam(":price", $price, PDO::PARAM_INT);
$query->bindParam(":type", $type, PDO::PARAM_INT);
$query->bindParam(":creatorId", $userId, PDO::PARAM_INT);
$query->execute();

$assetId = upload::getAssetIdFromName($filename);

$gd->file_new_name_body   = "$assetId";
$gd->process('/asset/');

if($gd->processed) {
    api::respond(200, true, "Successfully uploaded $filename");
} else {
    api::respond(400, false, "".$gd->error);
}

