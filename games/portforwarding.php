<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
?>
   <div class="jumbotron shadow-sm">
      <h1 class="display-4"><img height="64" width="64" src="/img/mercury.png"> Port Forwarding on <?=SITE_CONFIG["site"]["name"]?></h1>
      <p class="lead">An in-depth guide to port forwarding.</p>
   </div>
   <div class="card shadow-sm" style="border-radius: 0px;">
      <div class="card-body">
         <h4 class="font-weight-normal">Preface</h4>        
         <p>Before you start hosting a <?=SITE_CONFIG["site"]["name"]?> server, you'll need to know how to port forward. Without it, nobody on your local network will be able to connect to your server.</p>
         <p>With that said, there are two ways to host a server. The first way is by using UPnP port forwarding. The second way is by using Router port forwarding. To put it simply, UPnP forwarding is typically much easier and quicker while Router forwarding is a bit more complex. You should only really use Router forwarding if UPnP doesn't work, and there's a number of YouTube tutorials on how to do that. Here, we'll be covering UPnP forwarding.</p>
         <h4 class="font-weight-normal">UPnP forwarding</h4>
         <p>UPnP forwarding is the more simple route to port forwarding. All you'll need to do is download a utility that can handle the stuff for you. We recommend using <a href="https://github.com/kaklakariada/portmapper/releases">portmapper</a>, and is what we'll be using for this example. It does require Java, so go ahead and install that if you don't have it.</p>
         <p>First thing you'll want to do here is make it connect to the router, which you do by clicking <code>Connect</code>.</p>
         <img src="/img/portforwarding/portmapper_1.png" class="img-fluid">
         <p>If it's successful, the Port Mappings table should no longer be grey. You may even see some ports already set by other programs. To actually map a port, open up the mapping presets by clicking <code>Create</code>.</p>
         <img src="/img/portforwarding/portmapper_2.png" class="img-fluid">
         <p>With it open, you should first set a description. Doesn't matter what it is. Afterwards, click <code>Add</code> and fill out the fields. The protocol should set to <code>UDP</code>, and both the internal and external ports should be the port you configured for the server. With all of that set, you can save the presets.</p>
         <img src="/img/portforwarding/portmapper_3.png" class="img-fluid">
         <p>Now there's one last step. Select the preset you just created, and click <code>Use</code>.</p>
         <img src="/img/portforwarding/portmapper_4.png" class="img-fluid">
         <p>And just like that, you have port forwarded! Enjoy playing on your now-accessible server.</p>
      </div>
   </div>
<?php pageBuilder::buildFooter(); ?>