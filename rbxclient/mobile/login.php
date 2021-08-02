<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] == "POST")
{
  $usernameAndOTP = $_POST['username'] ?? false;
  $password = $_POST['password'] ?? false;
  if(!$usernameAndOTP || !$password) die(json_encode(["Status" => "MissingRequiredField"]));

  $username = explode("-", $usernameAndOTP)[0];
  $code = explode("-", $usernameAndOTP)[1] ?? 0;

  $userInfo = users::getUserInfoFromUserName($username);
  if(!$userInfo) die(json_encode(["Status" => "MissingRequiredField"]));
  if(!password_verify($password.SITE_CONFIG["site"]["pwsalt"], $userInfo->password)) die(json_encode(["Status" => "InvalidPassword"]));
  
  if($userInfo->twofa)
  {
    $gauth = twofa::initialize();
    $recoveryCodes = json_decode($userInfo->twofaRecoveryCodes, true);

    if (!$code) die(json_encode(["Status" => "MissingRequiredField"]));
    else if (is_numeric($code) && !$gauth->checkCode($userInfo->twofaSecret, $code, 1)) 
      die(json_encode(["Status" => "MissingRequiredField"]));
    else if (!is_numeric($code) && (!isset($recoveryCodes[$code]) || !$recoveryCodes[$code]))
      die(json_encode(["Status" => "MissingRequiredField"]));
    else
    {
      //invalidate recovery code
      if(!is_numeric($code))
      {
        $recoveryCodes[$code] = false;
        $recoveryCodes = json_encode($recoveryCodes);
        $query = $pdo->prepare("UPDATE users SET twofaRecoveryCodes = :recoveryCodes WHERE id = :uid");
        $query->bindParam(":recoveryCodes", $recoveryCodes, PDO::PARAM_STR);
        $query->bindParam(":uid", $uid, PDO::PARAM_INT);
        $query->execute();
      }
    }
  }

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