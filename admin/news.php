<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();

if(!SESSION) redirect("/");

if(SESSION["adminLevel"] < 2) {
  redirect("/dashboard");
}

$newsInfo = news::getNewsInfo(1);

//mkdown
$markdown = new Parsedown();
$markdown->setMarkupEscaped(true);
$markdown->setBreaksEnabled(true);
$markdown->setSafeMode(true);
$markdown->setUrlsLinked(true);

?>
<div class="app py-4 container">
   <h3 class="mb-4 font-weight-normal">News</h3>
   <div class="row">
      <div class="col">
         <h3 class="mb-4 font-weight-normal">Send News Update</h3>
         <div class="card">
            <div class="card-body">
               <div class="form-group">
                  <label for="newstitle">Title</label>
                  <input type="text" class="form-control" id="newstitle" placeholder="50 characters max">
               </div>
               <div class="form-group">
                  <label for="newsupdate">Body</label>
                  <textarea class="form-control" id="newsupdate" rows="3">Max 1000 characters - Markdown is supported</textarea>
               </div>
               <button type="submit" class="btn btn-success" data-control="updateNews"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Post News</button>
               <button type="submit" class="btn btn-outline-info" data-control="updateWebhook"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Post To Webhook</button>
               <script> 
                  $('button[data-control="updateNews"]').on('click', this, function()
                  {
                  var button = this; 
                  $(button).attr("disabled", "disabled").find("span").show();
                  $.post('/api/admin/updateNews', {"body":$("#newsupdate").val(), "title":$("#newstitle").val()}, function(data)
                  {
                    if(data.success){ toastr["success"](data.message); }
                    else{ toastr["error"](data.message); }
                    $(button).removeAttr("disabled").find("span").hide();
                  });
                  });

                  $('button[data-control="updateWebhook"]').on('click', this, function()
                  {
                  var button = this; 
                  $(button).attr("disabled", "disabled").find("span").show();
                  $.post('/api/admin/updateNewsWebhook', {"body":$("#newsupdate").val(), "title":$("#newstitle").val()}, function(data)
                  {
                    if(data.success){ toastr["success"](data.message); }
                    else{ toastr["error"](data.message); }
                    $(button).removeAttr("disabled").find("span").hide();
                  });
                  });
               </script>
            </div>
         </div>
      </div>
      <div class="col">
         <h3 class="mb-4 font-weight-normal">Currently displaying</h3>
         <div class="card shadow-sm">
            <div class="card-body">
               <h5 class="text-uppercase text-muted mb-2">News - <?=$newsInfo->title?> - <?=date("n/j/Y", $newsInfo->date)?></h5>
               <p class="card-text"><?=$markdown->text($newsInfo->news)?> - <?=users::getUsernameFromUid($newsInfo->createdBy);?></p>
            </div>
         </div>
         <hr>
         <h3 class="mb-4 font-weight-normal">Information</h3>
         <div class="card shadow-sm">
            <div class="card-body">
               <p class="card-text">The news function posts feed to EVERY user on-site, and it can be viewed on the dashboard. Please make sure to use formal language and keep swearing minimal.</p>
            </div>
            <div class="card-footer text-muted">
            The last feed was posted on <strong><?=date("n/j/Y", $newsInfo->date)?></strong> at <strong><?=date("h:i:sa", $newsInfo->date)?></strong> by <strong><?=users::getUsernameFromUid($newsInfo->createdBy);?></strong>
            </div>
         </div>
      </div>
      
   </div>
</div>
<?php
pageBuilder::buildFooter();
?>