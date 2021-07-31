<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

$newsInfo = news::getNewsInfo(1);
//mkdown
$markdown = new Parsedown();
$markdown->setMarkupEscaped(true);
$markdown->setBreaksEnabled(true);
$markdown->setSafeMode(true);
$markdown->setUrlsLinked(true);

pageBuilder::buildHeader();
?>
<style>
   .merc {
   font-family: "wf_SegoeUILight","wf_SegoeUI","Segoe UI Light","Segoe WP Light","Segoe UI","Segoe","Segoe WP","Tahoma","Verdana","Arial","sans-serif";
   }
</style>
<div class="app py-4 container">
<?php if(SESSION["ssc"]) { ?>
<div class="alert alert-danger" role="alert">
  Your account has been restricted. You cannot message, create invites, use the forums or chat in-game. Appeals can be sent to Discord.
</div>
<?php } ?>
   <h3 class="font-weight-normal mb-3">Hello, <?=SESSION["userName"]?>!</h3>
   <div class="row">
      <div class="col-sm-3">
         <div class="card shadow-sm">
            <div class="card-body text-center">
               <img src="/thumbnails/avatar?id=<?=SESSION["userId"]?>&x=250&y=250" alt="<?=SESSION["userName"]?>" class="img-fluid">
            </div>
         </div>
         <br>
         <h1 class="text-center merc"><?=SITE_CONFIG["site"]["name"]?></h1>
      </div>
      <div class="col-sm-9">
         <?php if(SESSION["adminLevel"] >= 2) { ?>
         <div id="pluginObjDiv">
            <object id="robloxnpapipluginobj" type="application/x-vnd-roblox-launcher">
            <object id="robloxactivexpluginobj" classid="clsid:76D50904-6780-4C8B-8986-1A7EE0B1716D" codebase="www.roblox.com" type="application/x-vnd-roblox-launcher">
         </div>
         <script> 
         function log(msg)
         {
            var test = "a";
            if($(".debug-log").text().length == 0)
               $(".debug-log").text(msg + "\n");
            else
               $(".debug-log").text($(".debug-log").text() + msg + "\n");
         }

         function testApp()
         {
            log("Properties of window.external: " + JSON.stringify(window.external));
            log("Properties of ActiveX plugin object: " + JSON.stringify(document.getElementById('robloxactivexpluginobj')));
            log("Properties of NPAPI plugin object: " + JSON.stringify(document.getElementById('robloxnpapipluginobj')));

            log("")
            log("Trying window.external launch");
            try
            {
               window.external.StartGame("ticket", "http://banland.xyz/", "http://banland.xyz/Game/Visit.ashx");
            }
            catch (e) 
            {
               log("window.external launch failed");
            }

            log("Trying playFromUrl launch");
            try
            {
               parent.playFromUrl("http://banland.xyz/Game/Visit.ashx");
            }
            catch (e)
            {
               log("playFromUrl launch failed");
            }
         }
         </script>
         <h3 class="font-weight-normal">App Debug</h3>
         <div class="card shadow-sm">
            <div class="card-body">
               <p class="card-text">HTTP_USER_AGENT: <code><?=$_SERVER["HTTP_USER_AGENT"] ?? "N/A"?></code></p>
               <p class="card-text">HTTP_X_REQUESTED_WITH: <code><?=$_SERVER["HTTP_X_REQUESTED_WITH"] ?? "N/A"?></code></p>
               <button class="btn btn-success" onclick="testApp()">Debug</button>
               <h5 class="text-uppercase text-muted mt-2">Debug Log</h5>
               <pre class="debug-log"></pre>
            </div>
         </div>
         <br>
         <?php } ?>
         <h3 class="font-weight-normal">Your Wall</h3>
         <div class="card shadow-sm">
            <div class="card-body">
               <h5 class="text-uppercase text-muted mb-2">Wall</h5>
               <p class="card-text">Stats will be posted here!</p>
            </div>
         </div>
         <br>
         <div class="row">
            <div class="col">
               <div class="card">
                  <div class="card-body">
                     <div class="row align-items-center gx-0">
                        <div class="col">
                           <h6 class="text-uppercase text-muted mb-2">
                              Friends
                           </h6>
                           <span class="h2 mb-0">
                           0
                           </span>
                        </div>
                        <div class="col-auto">
                           <i class="fas fa-user-friends"></i>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col">
               <div class="card">
                  <div class="card-body">
                     <div class="row align-items-center gx-0">
                        <div class="col">
                           <h6 class="text-uppercase text-muted mb-2">Rocks</h6>
                           <span class="h2 mb-0"><?=SESSION["currency"]?></span>
                        </div>
                        <div class="col-auto">
                        <i class="fas fa-gem"></i>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col">
               <div class="card">
                  <div class="card-body">
                     <div class="row align-items-center gx-0">
                        <div class="col">
                           <h6 class="text-uppercase text-muted mb-2">
                              Random
                           </h6>
                           <span class="p mb-0"><?=users::generateRandomFact()?></span>
                        </div>
                        <div class="col-auto">
                        <i class="fas fa-random"></i>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <br>
         <div class="card shadow-sm">
            <div class="card-body">
               <h5 class="text-uppercase text-muted mb-2">News - "<?=$newsInfo->title?>" - <?=date("n/j/Y", $newsInfo->date)?></h5>
               <p class="card-text"><?=$markdown->text($newsInfo->news)?> - <?=users::getUsernameFromUid($newsInfo->createdBy);?></p>
            </div>
         </div>
         </div>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>
