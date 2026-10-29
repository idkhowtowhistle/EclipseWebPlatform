<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!SESSION) { api::respond(405, false, "Method Not Allowed"); }
// if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(!SESSION["adminLevel"] || SESSION["adminLevel"] < 2){api::respond(400, false, "Not an administrator!"); }
if(!isset($_POST["bannerId"]) || !isset($_POST["active"])) { api:: respond(400, false, "Invalid Request"); }
if(!trim($_POST["bannerId"])) {api::respond(400, false, "Invalid Request");}
if(!is_numeric($_POST["bannerId"]) || !is_numeric($_POST["active"])) {api::respond(400, false, "Invalid Request");}
if($_POST["bannerId"] < 0) {api::respond(400, false, "Invalid Request");}
api::lastAction();

$bannerId = $_POST["bannerId"];
$active = $_POST["active"];

$banner = admin::getBannerInfo($bannerId);

if(!$banner){api::respond(400, false, "This banner does not exist.");}

if($active < 0 || $active > 1) {api::respond(400, false, "Invalid Request");}

$query = $pdo->prepare("UPDATE banners SET active = :active WHERE id = :bannerId");
$query->bindParam(":active", $active, PDO::PARAM_INT);
$query->bindParam(":bannerId", $bannerId, PDO::PARAM_INT);
$query->execute();

$respond = [
    0 => "Deactivated banner #$bannerId",
    1 => "Activated banner #$bannerId"
];

api::respond(200, true, $respond[$active]);