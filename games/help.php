<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
pageBuilder::buildHeader();
?>
<h3 class="font-weight-normal">About Self Hosted Servers</h3>
<p>In Mercury, our games are hosted by regular users on the site that users can then join via the site. This is called "self-hosting" and is not without its vulnerabilities. Here is some security things you should look out for when hosting/playing a self-hosted gameserver.</p>
<h3 class="font-weight-normal">Playing a server</h3>
<p>It is possible for the server hoster to get your IP address via a network analyzer.<br>This really isn't anything to be worried about as it requires effort and intent to actually get the IP itself. You can be extra safe by using a VPN when playing a server or playing servers hosted by trusted users.</p>
<h3 class="font-weight-normal">Hosting a server</h3>
<p>It is also possible for a player to get your IP address via a web debugging proxy but it should be noted that they can only get your IP and not any other IPS connected to the server. You can also use a VPN if you want to do so to be more safe.</p>
<h3>Now that you are aware, have fun!</h3>

<?php pageBuilder::buildFooter(); ?>