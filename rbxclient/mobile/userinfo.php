<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
header("content-type: application/json");
if (!SESSION) die();

$userInfoResponse = 
[
  "UserName" => SESSION["userName"],
  "RobuxBalance" => SESSION["currency"],
  "TicketsBalance" => 0,
  "IsAnyBuildersClubMember" => false,
  "ThumbnailUrl" => siteBaseUrl()."/thumbnails/avatar?id=".SESSION["userId"]."&x=250&y=250",
  "UserID" => SESSION["userId"]
];

die(json_encode($userInfoResponse));