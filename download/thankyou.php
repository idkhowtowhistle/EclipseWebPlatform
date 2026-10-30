<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
?>
<style>
   .coolhr {
   margin: 0;
   margin-bottom: 10px;
   border: none;
   width: 400px;
   height: 1px;
   opacity: 0.5;
   background: linear-gradient( to right, red 20%, yellow 20%, yellow 36%, green 36%, green 60%, blue 60%, blue 100% );
   }
</style>
<div class="app py-4 container">
   <div class="jumbotron shadow-sm">
      <h1 class="display-4"><img height="56" width="54" src="/img/eclipse.png"> Thank you for downloading <?=SITE_CONFIG["site"]["name"]?></h1>
   </div>
   <h3 class="font-weight-normal">Get started by joining your first <a href="/games">game</a>!</h3>
   <p>Or, have fun in <?=SITE_CONFIG["site"]["name"]?> Studio!</p>

   <p>If you haven't registered for an account, <a href="/register">go ahead</a>!</p>
</div>
<?php pageBuilder::buildFooter(); ?>