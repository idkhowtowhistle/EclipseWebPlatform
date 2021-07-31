<?php 
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php'; 

$uid = $_GET['userId'] ?? $_GET['userid'] ?? false;
$polygonUidMap = 
[
	1 => 6, // task
	9 => 3, // bucks
	2 => 4, // multako
	4 => 30, // debian
	7 => 50, // trout
	11 => 12, // chess
	13 => 7, // yimy
	19 => 93, // unexp
	27 => 1, // pizzaboxer
	28 => 24, // kjf
	97 => 26, // heree
	90 => 91, // cole
	91 => 95, // hitius
	55 => 82, // carrot
	164 => 71 //chadwarden
];

header("Pragma: no-cache");
header("Cache-Control: no-cache");
if(isset($polygonUidMap[$uid])) header("Location: http://chef.pizzaboxer.xyz/Asset/CharacterFetch.ashx?userId=".$polygonUidMap[$uid]."&assetHost=banland.xyz");