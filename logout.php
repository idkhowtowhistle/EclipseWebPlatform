<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if(SESSION)
session::logout(SESSION["sessionKey"]);
session::invalidate(SESSION["sessionKey"]);


redirect("/");