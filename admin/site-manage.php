<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

users::requireLogin();
if(SESSION["adminLevel"] < 2){redirect("/");}

pageBuilder::buildHeader();
?>
<a href="/shawtylikeamelody">PHPMyAdmin</a>
<?php pageBuilder::buildFooter(); ?>