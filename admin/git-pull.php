<?php
header("content-type: text/plain");

function sendSystemWebhook($message) 
{
	// example payload:
	// $payload = ["username" => "test", "content" => "test", "avatar_url" => "https://polygon.pizzaboxer.xyz/thumbs/avatar?id=1&x=100&y=100"];
	$payload = ["content" => $message];
	$ch = curl_init();  

	curl_setopt($ch, CURLOPT_URL, "https://discord.com/api/webhooks/869661891490873414/biY4om6mqXlpZkjVI2v1Xul5PpyvZfM3p7XQpXCeANr8W6YdwboyxawWXn6hZyWfg73V");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['payload_json' => json_encode($payload)]));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}

// this should only be used if core.php does not work
$emergency = ($_GET["key"] ?? false) == "D5F6E2EAA6C07C991CA2895920A8BBA8BB66CA16";
$output = "";
$webhook = "";
$output_array = [];

if($emergency)
{
	$webhook .= "Git Pull intiated by [[[EMERGENCY]]]\n";
}
else
{
	require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";
	if(!SESSION || SESSION["adminLevel"] < 2) die(http_response_code(404));
	$webhook .= "Git Pull intiated by ".SESSION["userName"]."\n";
}

exec("git pull 2>&1", $output_array, $exitcode);

foreach($output_array as $line) $output .= "$line\n";
if($exitcode != 0) $output .=  "\n\n Git exited with code $exitcode";

echo $output;

$webhook .= "Output:\n";
$webhook .= "```bash\n";
$webhook .= $output;
$webhook .= "```";

sendSystemWebhook($webhook);
