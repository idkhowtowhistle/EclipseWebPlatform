<?php 
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php'; 

$uid = $_GET['userId'] ?? $_GET['userid'] ?? false;

header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Location: http://chef.pizzaboxer.xyz/Asset/BodyColors.ashx?".$_SERVER["QUERY_STRING"]);