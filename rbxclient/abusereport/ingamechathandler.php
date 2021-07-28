<?php
require $_SERVER['DOCUMENT_ROOT']."/api/private/core.php";
header("content-type: text/plain; charset=utf-8");

$webhook = "";
function sendSystemWebhook($message) 
{
	// example payload:
	// $payload = ["username" => "test", "content" => "test", "avatar_url" => "https://polygon.pizzaboxer.xyz/thumbs/avatar?id=1&x=100&y=100"];
	$payload = ["content" => $message];
	$ch = curl_init();  

	curl_setopt($ch, CURLOPT_URL, "https://discord.com/api/webhooks/870048492096192542/RWnJrWqKulFqfZ0Htx2fX5CF2vDc1dgATXCw-D9lyGtHJZNMvBXFww7Kzbjrv8dRQFXG");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['payload_json' => json_encode($payload)]));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

if($_SERVER['HTTP_USER_AGENT'] != "Roblox/WinInet") die("ERROR: Dumb nigga detected");

$ticket = $_GET['ticket'] ?? false;
$gameInfo = games::getGameInfoFromTicket($ticket);
if(!$gameInfo) die("ERROR: Invalid server ticket");

/* $xml = '<report userID="27">
	<comment>AbuserID:1;Dating;being a nigger</comment>
	<messages>
		<message userID="1" guid="{DE0C5567-03E9-4273-9666-936529857EE3}">NIGGER</message>
		<message userID="1" guid="{25B6D7E9-029D-48DA-8610-9642FE4536CB}">ya yeet</message>
		<message userID="2" guid="{25B6D7E9-029D-48DA-8610-9642FE4536CB}">nigger faggot</message>
	</messages>
</report>'; */

$xml = file_get_contents("php://input");
$xml_parsed = simplexml_load_string($xml);
$abuses = 
[
	"Swearing",
	"Bullying",
	"Scamming",
	"Dating",
	"Cheating/Exploiting",
	"Personal Questions",
	"Offsite Links",
	"Bad Model or Script",
	"Bad Username",
	"Bad Words or Threats",
	"Talking about Dating",
	"Account Trading or Sharing",
	"Asking Personal Questions",
	"Rude or Mean Behavior",
	"False Reporting Me"
];

if(!$xml_parsed) die("ERROR: Invalid XML");
$xml_parsed = (array)$xml_parsed;

$reporterID = $xml_parsed["@attributes"]["userID"] ?? false;
$report = $xml_parsed["comment"] ?? false;
$messages = $xml_parsed["messages"];

$report_parts = explode(";", $report);
$abuserID = explode(":", $report_parts[0])[1];
$abuse = $report_parts[1];
$description = $report_parts[2];

//if(!games::checkIfPlayerInServer($reporterID, $gameInfo->id))
	//die("ERROR: Reporter is not in server");

//if(!games::checkIfPlayerInServer($abuserID, $gameInfo->id))
//	die("ERROR: Abuser is not in server");

if(!in_array($abuse, $abuses))
	die("ERROR: Invalid abuse detail");

if(strlen($description) > 128)
	die("ERROR: Description is too long");

$message_array = [];

foreach($messages as $message)
{
	$message_array[] = 
	[
		"UserID" => (int)$message->attributes()["userID"], 
		"Message" => (string)$message[0]
	];
}

$message_array = array_reverse($message_array);
$message_json = json_encode($message_array);

db::run(
	"INSERT INTO reports (reporterID, abuserID, abuse, description, messages, time) VALUES (:reporterID, :abuserID, :abuse, :description, :messages, UNIX_TIMESTAMP())",
	[":reporterID" => $reporterID, ":abuserID" => $abuserID, ":abuse" => $abuse, ":description" => $description, ":messages" => $message_json]
);

$webhook .= "New report received at " . date('d/m/Y h:i:s A') . "\n";
$webhook .= "Reporter: " . users::getUsernameFromUid($reporterID) . "\n";
$webhook .= "Abuser: " . users::getUsernameFromUid($abuserID) . "\n";
$webhook .= "Reason: " . $abuse . "\n";
if(strlen($description)) $webhook .= "Comment: `" . str_replace("`", "", $description) . "`\n";
if(count($message_array)) 
{
	$webhook .= "Chat logs (last 6 messages): \n";
	$webhook .= "```";
	foreach (array_slice($message_array, -6, 6) as $message)
	{
		$webhook .= users::getUsernameFromUid($message["UserID"]) . ": " . str_replace("`", "", $message["Message"]) . "\n";
	}
	$webhook .= "```";
}

sendSystemWebhook($webhook);

die("OK");