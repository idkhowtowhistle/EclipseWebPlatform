<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

if($_SERVER['REQUEST_METHOD'] == "POST")
{
  $username = $_POST['username'] ?? false;
  $password = $_POST['password'] ?? false;
  $userInfo = users::getUserInfoFromUserName($username);

  if(!$username || !$password) die("MissingRequiredField");
  if(!$userInfo) die("InvalidUsername")
  if(!password_verify($password.SITE_CONFIG["site"]["pwsalt"], $userInfo->password)) die("InvalidPassword");

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