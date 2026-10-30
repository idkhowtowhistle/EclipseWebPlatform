<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::$pageConfig["title"] = "About";
pageBuilder::buildHeader();
?>
<div class="app py-4 container">
<div class="jumbotron shadow-sm">
  <h1 class="display-4"><img height="64" width="64" src="/img/mercury.png"> Welcome to <?=SITE_CONFIG["site"]["name"]?></h1>
  <p class="lead">we hope you enjoy yo time</p>
</div>
<div class="card shadow-sm" style="border-radius: 0px;">
  <div class="card-body">
    <p>Welcome to <?=SITE_CONFIG["site"]["name"]?>, a private server for an old rendition of a popular brickbuilding MMO. 
       Developed by IdkHowToWx, we pride ourselves on having the most <strong>secure</strong> and <i>efficient</i> web & client interface,
       while maintaining a safe and fun experience for our users.</p>
    <p>We decided to create this private server because we felt the quality of other private servers was not optimal for long term usability.
       While some private servers mention their community, they usually have poorly managed administrators, 
       and while some private servers say they are secure, they usually have issues with their community, and later break apart.
       <?=SITE_CONFIG["site"]["name"]?> does not aim to "steal members", we're there when you need a <i>proper private server</i>.
       By using our private server and its services, you can trust us for our transparency and our reliable interfaces. We're like the <b>best of both worlds</b>.
    </p>
    <p>
       We have a full, feature packed user experience containing: a catalog, user profiles, character customization, and fun games to play!
       Currently, we are planning for self-hosted servers, but we will 100% have dedicated servers for security.
    </p>
    <hr class="mb-3">
    <h4 class="font-weight-normal">Enough talk, enjoy your experience! :) </h4>
    <small class="text-muted">- From the <?=SITE_CONFIG["site"]["name"]?> team</small>
  </div>
</div>
</div>
<?php pageBuilder::buildFooter(); ?>