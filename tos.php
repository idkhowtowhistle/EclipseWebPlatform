<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::$pageConfig["title"] = "TOS";
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
      <h1 class="display-4"><img height="56" width="54" src="/img/eclipse.png"> <?=SITE_CONFIG["site"]["name"]?> Terms Of Service</h1>
      <p class="lead">All members who have registered to <?=SITE_CONFIG["site"]["name"]?> agree to these terms after they have registered. </p>
   </div>
   <div class="card shadow-sm" style="border-radius: 0px;">
      <div class="card-body">
         <h4 class="font-weight-normal">
            General Terms Of Service
         </h4>
         <p>We have the right to deny you our <b>online</b> services (site, client) if you have broken any of our terms of services noted.</p>
         <small class="text-muted">Please note, terms of service is different to our site rules.</small>
         <hr>
         <h4 class="font-weight-normal">
            Disclaimer
         </h4>
         <p><?=SITE_CONFIG["site"]["name"]?> does not own the ROBLOX trademark, or any of the ROBLOX Corp subsidaries. We do not own their clients, and all respective rights belong to ROBLOX Corp.</p>
         <hr>
         <p>After registration, you agree to:</p>
         <ol>
            <li>Not upload any copyrighted content (music, shirts, or any asset that is copyrighted) to <?=SITE_CONFIG["site"]["name"]?></li>
            <li>Anything that infringes on others rights</li>
            <li>You are not allowed to access any data you aren't allowed to access using our services or use bots to automate anything on our website.</li>
            <li>You cannot exploit our services to your advantage (game client, farming Rocks)</li>
            <li>You must be over the age of 13</li>
            <li>Reading the <a href="/rules">Site Rules</a></li>
         </ol>
         <p>We are not responisble if you have clicked on a malicious link posted by another user, but you always can report a user for inappropriate behaviour.</p>
         <hr class="coolhr">
         <h4 class="font-weight-normal">Thank you for reading.</h4>
      </div>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>