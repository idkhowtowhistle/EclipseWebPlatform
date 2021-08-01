<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if (!SESSION) die();

$userInfoResponse = 
[
  "UserName" => SESSION["userName"],
  "RobuxBalance" => SESSION["currency"],
  "TicketsBalance" => 0,
  "IsAnyBuildersClubMember" => false,
  "ThumbnailUrl" => "http://banland.xyz/thumbnails/avatar?id=".SESSION["userId"]."&x=250&y=250",
  "UserID" => SESSION["userId"]
];

  die(json_encode($userInfoResponse));