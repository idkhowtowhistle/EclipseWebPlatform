<?php
require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";

$id = $_GET['ID'] ?? $_GET['id'] ?? false;
if(!is_numeric($id)) die(http_response_code(400));

if(file_exists("./corescripts/$id.lua"))
{
	header('Content-Type: binary/octet-stream'); 
	header('Content-Disposition: attachment; filename="'.md5_file("./corescripts/$id.lua").'"'); 
	
	$data = file_get_contents("./corescripts/$id.lua");
	$data = str_ireplace("roblox.com/asset", "banland.xyz/asset", $data);
	if($id != 38037265) $data = RBX::SignData($data, $id);

	die($data);
}

if(file_exists("/var/www/pizzaboxer.xyz/polygon/asset/files/$id"))
{
	die(header("Location: http://chef.pizzaboxer.xyz/asset/?".$_SERVER["QUERY_STRING"]));
}

die(header("Location: https://assetdelivery.roblox.com/v1/asset/?".$_SERVER["QUERY_STRING"]));
