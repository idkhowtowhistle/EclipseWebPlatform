<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if (SESSION) {
   redirect("/dashboard");
}
pageBuilder::buildHeader();
?>
<style>
   .jumbotron {
      background: url("/img/indexbg1.png") no-repeat center center;
      -webkit-background-size: 100% 100%;
      -moz-background-size: 100% 100%;
      -o-background-size: 100% 100%;
      background-size: 100% 100%;
      color: white;
   }
</style>
<div class="container">
   <div class="jumbotron" id="#bgjumbotron">
      <div class="row">
         <div class="col">
            <h1 class="font-weight-normal">Welcome to <?= SITE_CONFIG["site"]["name"] ?></h1>
            <h5 class="font-weight-normal">Attention Kryptonians, we're relocating to a new planet...</h5>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-sm-6">
         <div class="card hover-shadow rounded-0">
            <div class="card-body">
               <h5 class="card-title">Invite Key Protected <i class="fas fa-user-lock"></i></h5>
               <p class="card-text">We require you have an invite key to gain entry to <?= SITE_CONFIG["site"]["name"] ?>. You can ask your friends for a key!</p>
               <button class="btn btn-sm btn-success" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                  Read More
               </button>
               <div class="collapse" id="collapseExample">
                  <hr>
                  <div class="card card-body">
                     Alternatively you can message task @ task#6636 on Discord, just mention <?= SITE_CONFIG["site"]["name"] ?> and imply you need a key. Discord is the quickest way to get a response for any question you have about <?= SITE_CONFIG["site"]["name"] ?>. A reminder you must be 13+ to use this service. We are not affiliated with Discord.
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-sm-6">
         <div class="card hover-shadow rounded-0">
            <div class="card-body">
               <h5 class="card-title">Social Media <i class="fab fa-discord"></i> <i class="fab fa-youtube"></i> <i class="fab fa-twitter"></i></h5>
               <div class="embed-responsive embed-responsive-16by9">
                  <iframe class="embed-responsive-item" src="https://www.youtube-nocookie.com/embed/3PVleglv3lk" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
               </div>
               <small class="text-muted">8.5/10 (cred. <a href="https://www.youtube.com/channel/UCzw-myqzUucY0nJfLIAsCZA">pastq)<a></small>
            </div>
            <div class="card-footer">
               <small class="text-muted">Want to check out more content about Mercury?</small>
               <a href="/gallery" type="button" class="btn btn-sm btn-success">Gallery</a>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   $("#bgjumbotron").hover(function() {
      $(this).css('background-image','url(/img/indexbg1.png)')
   }, function() {
      $(this).css('background-image','url(/img/indexbg2.png)')
   })
</script>
<?php pageBuilder::buildFooter(); ?>