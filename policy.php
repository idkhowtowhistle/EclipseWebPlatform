<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
?>
<div class="app py-4 container">
   <div class="jumbotron shadow-sm">
      <h1 class="display-4"><img height="64" width="64" src="/img/mercury.png"> <?=SITE_CONFIG["site"]["name"]?> Privacy Policy</h1>
   </div>
   <div class="card shadow-sm" style="border-radius: 0px;">
      <div class="card-body">
        <h4 class="font-weight-normal">Information we collect on site</h4>
        <p>We do not collect any personal information, except your E-Mail and IP address.
           Passwords are hashed with BCRYPT with an additional salt for extra security.
        </p>
        <p>
        When you log into the website, we store your IP address again and your user agent.
        </p>
        <h4 class="font-weight-normal">How we use this information</h4>
        <p>
        We require your IP address to interact with the game client and we may use it as an identifier to prevent unauthorized access. 
        We also require your user agent for account security.
        </p>
        <p>Mercury does not provide any third party with your information, but services we use on-site may collect analytics (e.g. ads)</p>
        <h4 class="font-weight-normal">Additional information</h4>
        <p>
        We require cookies for the main site to function, mainly your session cookie - which helps us identify your account.
        Some services we use may collect additional yet optional cookies.
        </p>
        <p>
        If a game is <b>self-hosted</b> it is possible for your peer to access your IP address using a network sniffer. 
        This is possible to prevent if you have a VPN to mask your IP address while in game. 
        </p>
      </div>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>