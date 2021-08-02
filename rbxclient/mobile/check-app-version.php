<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
header("content-type: application/json");

die(json_encode(["data" => ["UpgradeAction" => "None"]]));