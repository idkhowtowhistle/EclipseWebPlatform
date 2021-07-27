<?php
header("content-type: text/plain");

// this should only be used if core.php does not work
$emergency = ($_GET["key"] ?? false) == "D5F6E2EAA6C07C991CA2895920A8BBA8BB66CA16";

if(!$emergency)
{
	require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";
	if(SESSION["adminLevel"] < 2) pageBuilder::errorCode(404);
}

// todo: add webhook stuff

$output = [];
exec("git pull 2>&1", $output, $exitcode);
foreach($output as $line) echo "$line<br>";
if($exitcode != 0) echo "<br><br> Program exited with code $exitcode";