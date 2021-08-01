<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
header("content-type: application/json");

if(!SESSION) die();
session::logout(SESSION["sessionKey"], false, true);