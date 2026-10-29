<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
// if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(!SESSION["adminLevel"] || SESSION["adminLevel"] < 2){api::respond(400, false, "Not an administrator!"); }
if(!isset($_POST["body"])) { api:: respond(400, false, "Invalid Request"); }
if(!isset($_POST["title"])) { api:: respond(400, false, "Invalid Request"); }
if(!trim($_POST["body"])) {api::respond(400, false, "Invalid Request");}
if(!trim($_POST["title"])) {api::respond(400, false, "Invalid Request");}
if(!htmlspecialchars($_POST["body"])) {api::respond(400, false, "Invalid Request");}
if(!htmlspecialchars($_POST["title"])) {api::respond(400, false, "Invalid Request");}
if(strlen($_POST["body"]) > 1000 || strlen($_POST["body"]) < 3) {api::respond(400, false, "You need to make the body less than 1000 characters or more than 3");}
if(strlen($_POST["title"]) > 50 || strlen($_POST["title"]) < 3) {api::respond(400, false, "You need to make the title less than 50 characters or more than 3");}
api::lastAction();

$body = $_POST["body"];
$creatorId = SESSION["userId"];
$creatorUsername = SESSION["userName"];
$title = $_POST["title"];

// PHP webhook implementation https://gist.github.com/Mo45/cb0813cb8a6ebcd6524f6a36d4f8862c
$webhookurl = "https://discord.com/api/webhooks/848807363833626644/q07zJNcbzwnZ-rQ7UYxH3P8VYXBXh3kE0VJMAcxk_rBsxhn8hUp6IW2hxcAlBTTFC2Re";
$timestamp = date("c", strtotime("now"));

$json_data = json_encode([
    // Message
    "content" => "@everyone - $title - Recent News",
    
    // Username
    "username" => SITE_CONFIG["site"]["name"]." News",

    // Text-to-speech
    "tts" => false,

    // Embeds Array
    "embeds" => [
        [
            // Embed Title
            "title" => "$title",

            // Embed Type
            "type" => "rich",

            // Embed Description
            "description" => "$body -$creatorUsername",

            // URL of title link
            "url" => "https://banland.xyz",

            // Timestamp of embed must be formatted as ISO8601
            "timestamp" => $timestamp,

            // Embed left border color in HEX
            "color" => hexdec( "3366ff" ),

            // Footer
            "footer" => [
                "text" => SITE_CONFIG["site"]["name"]." News",
            ],

            // Author
            "author" => [
                "name" => "$creatorUsername",
                "url" => "https://banland.xyz"
            ],
        ]
    ]

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


$ch = curl_init( $webhookurl );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );
// If you need to debug, or find out why you can't send message uncomment line below, and execute script.
// echo $response;
curl_close( $ch );

api::respond(200, true, "Webhook sent!");