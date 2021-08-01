<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] == "POST")
{
  $username = $_POST['username'] ?? false;
  $password = $_POST['password'] ?? false;
  $userInfo = users::getUserInfoFromUserName($username);

  if(!$username || !$password) die(json_encode(["Status" => "MissingRequiredField"]));
  if(!$userInfo) die(json_encode(["Status" => "InvalidUsername"]));
  if(!password_verify($password.SITE_CONFIG["site"]["pwsalt"], $userInfo->password)) die(json_encode(["Status" => "InvalidPassword"]));
  
  $userInfoResponse = 
  [
    "UserName" => $userInfo->username,
    "RobuxBalance" => $userInfo->currency,
    "TicketsBalance" => 0,
    "IsAnyBuildersClubMember" => false,
    "ThumbnailUrl" => "http://banland.xyz/thumbnails/avatar?id=".$userInfo->id."&x=250&y=250",
    "UserID" => $userInfo->id
  ];

  session::create($userInfo->id, false, true);
  die(json_encode(["Status" => "OK", "UserInfo" => $userInfoResponse]));
}

die(json_encode(["Status" => "MissingRequiredField"]));