<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(!SESSION){api::respond(400, false, "Invalid Request");}
if(!SESSION["adminLevel"] || SESSION["adminLevel"] < 2){api::respond(400, false, "Not an administrator!"); }
if(!isset($_POST["color"]) || !isset($_POST["text"]) || !isset($_POST["textColor"])){ api::respond(400, false, "Invalid Request"); }
if(!trim($_POST["text"])){api::respond(400, false, "You need to set a message");}
if(!trim($_POST["textColor"])){api::respond(400, false, "Invalid Request");}
if(!trim($_POST["color"])){api::respond(400, false, "Invalid Request");}
if(strlen($_POST["text"]) > 128 || strlen($_POST["text"]) < 3) {api::respond(400, false, "The message is either too large or too small (> 128 or < 3)");}
if($_POST["textColor"] != "light" && $_POST["textColor"] != "dark"){ api::respond(400, false, "Invalid Request"); }
if(!ctype_xdigit($_POST["color"]) && !strlen($_POST["color"])==6) {api::respond(400, false, "The hex color is invalid.");}
if($pdo->query("SELECT COUNT(*) FROM banners WHERE active")->fetchColumn() > 5){ api::respond(400, false, "There's too many banners currently active!"); }

api::lastAction();

$username = SESSION["userName"];
$color = $_POST["color"];
$textColor = "text-" . $_POST["textColor"];
$text = htmlspecialchars($_POST["text"]);

$query = $pdo->prepare("INSERT INTO banners (color, textcolor, username, message, date) VALUES (:color, :textcolor, :username, :message, UNIX_TIMESTAMP())");
$query->bindParam(":color", $color, PDO::PARAM_STR);
$query->bindParam(":textcolor", $textColor, PDO::PARAM_STR);
$query->bindParam(":username", $username, PDO::PARAM_STR);
$query->bindParam(":message", $text, PDO::PARAM_STR);
$query->execute();

api::respond(200, true, "Created a site banner! Activate it.");
