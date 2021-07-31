<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

if($_SERVER['REQUEST_METHOD'] == "POST")
{
  $username = $_POST['username'] ?? false;
  $password = $_POST['password'] ?? false;
  $userInfo = users::getUserInfoFromUserName($username);

  if(!$username || !$password) die(json_encode(["Status" => "MissingRequiredField"]));
  if(!$userInfo) die(json_encode(["Status" => "InvalidUsername"]));
  if(!password_verify($password.SITE_CONFIG["site"]["pwsalt"], $userInfo->password)) die(json_encode(["Status" => "InvalidPassword"]));
  die(json_encode(["Status" => "OK"]));

  session::create($userInfo->id);
  if($userInfo->twofa)
	{
    if($returnurl) die(header("Location: /auth/twofa?ReturnUrl=".$returnurl));
    die(header("Location: /auth/twofa")); 
	}
	else
	{
    if($returnurl) die(header("Location: ".$returnurl));
		die(header("Location: /")); 
	}
}