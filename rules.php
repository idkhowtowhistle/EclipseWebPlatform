<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::$pageConfig["title"] = "Rules";
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
      <h1 class="display-4"><img height="56" width="54" src="/img/eclipse.png"> <?=SITE_CONFIG["site"]["name"]?> Rules</h1>
      <p class="lead">Important</p>
   </div>
   <div class="card shadow-sm" style="border-radius: 0px;">
      <div class="card-body">
         <ol>
            <li>Toxicity (while subjective) is not allowed and will result in chat services removed.</li>
            <li>Users under the age of 13 will get their chat services removed or banned</li>
            <li>Racism of any sort posted on <?=SITE_CONFIG["site"]["name"]?> will result in chat services removed</li>
            <li>While we are pretty lenient on rules, we do not allow anything extremely offensive.</li>
            <li>Discussion about other private servers is not allowed. Keep it to a minimum.</li>
            <li>Posting untrustworthy links on any of our services will result in chat services removed.</li>
            <li>Spam or anything annoying repeated constantly will result in chat services removed.</li>
            <li>Nudity or anything NSFW is a straightforward ban.</li>
            <li>While our community is generally 13+, links to social media (YouTube exempt) should be sent carefully.</li>
         </ol>
         <p>When we mean removed, or banned - we mean it. We are a mostly <b>no appeal</b> private server.</p>
         <hr class="coolhr">
         <h4 class="font-weight-normal">Thank you for reading.</h4>
      </div>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>
