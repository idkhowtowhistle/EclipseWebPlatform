<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if (SESSION) {
   redirect("/dashboard");
}
pageBuilder::buildHeader();
?>
<h1>maintenance break</h1>