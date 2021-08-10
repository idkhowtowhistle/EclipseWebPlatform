<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");
header("referrer-policy: same-origin");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
upload::initialize();

api::requireLogin();

api::lastAction();

$userId = SESSION["userId"];
$file = $_FILES["file"] ?? false;
$name = $_POST["name"] ?? false;
$desc = $_POST["desc"] ?? false;
$price = $_POST["price"] ?? false;
$type = $_POST["type"] ?? false;

if(!$file) api::respond(400, false, "You need to upload a file!");
if(!in_array($file["type"], ["image/png", "image/jpg", "image/jpeg"])) api::respond(400, false, "Must be a .png or .jpg file");
if(strlen($name) < 1 || strlen($name) > 128) api::respond(400, false, "Asset name must be between 1-128 characters");
if(general::filterText($name, false, false) != $name) api::respond(400, false, "Asset name cannot contain inappropriate text");
if(!in_array($type, [2, 11, 12, 13])) api::respond(400, false, "You can't upload that type of content");

$query = db::run("SELECT createdOn FROM assets WHERE creatorId = :uid ORDER BY id DESC", [":uid" => $userId]);
$lastCreation = $query->fetchColumn();
if($lastCreation+30 > time()) api::respond(400, false, "Please wait ".(30-(time()-$lastCreation))." seconds before creating a new asset");

$asset = new Upload($file);
if(!$asset->uploaded) api::respond(400, false, "Something went wrong: Failed to process image");
$asset->allowed = ['image/png', 'image/jpg', 'image/jpeg'];
$asset->image_convert = "png";

api::respond(200, true, "something happened and it was good");