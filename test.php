<?php
$xml = '<report userID="27">
	<comment>AbuserID:1;Dating;being a nigger</comment>
	<messages>
		<message userID="1" guid="{DE0C5567-03E9-4273-9666-936529857EE3}">NIGGER</message>
		<message userID="1" guid="{25B6D7E9-029D-48DA-8610-9642FE4536CB}">ya yeet</message>
		<message userID="2" guid="{25B6D7E9-029D-48DA-8610-9642FE4536CB}">nigger faggot</message>
	</messages>
</report>';

$xml_parsed = (array)simplexml_load_string($xml);

if(!$xml_parsed || $_SERVER['HTTP_USER_AGENT'] != "Roblox/WinInet") die("Bad Request");

$userID = $xml_parsed["@attributes"]["userID"] ?? false;
$report = $xml_parsed["comment"] ?? false;
$messages = $xml_parsed["messages"];

$report_parts = explode(";", $report);
$abuserID = explode(":", $report_parts[0])[1];
$reason = $report_parts[1];
$comment = $report_parts[2];

echo "Reporter ID: ".$userID."<br>";
echo "Abuser ID: ".$abuserID."<br>";
echo "Reason: ".$reason."<br>";
echo "Comment: ".$comment."<br>";

echo "<br><br>";

foreach($messages as $message)
{
	echo "User ID: ".$message->attributes()["userID"]."<br>";
	echo "Message: ".$message[0]."<br>";
	echo "<br>";
}

var_dump($comment);