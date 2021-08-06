<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
?>
<div class="jumbotron">
    <div class="row">
        <div class="col">
            <h1 class="font-weight-normal"><?= SITE_CONFIG["site"]["name"] ?> Gallery</h1>
            <h5 class="font-weight-normal">Check out what our users have been posting and catch up with our media!</h5>
        </div>
    </div>
</div>

<?php pageBuilder::buildFooter(); ?>