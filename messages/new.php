<?php
require $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
pageBuilder::buildHeader();

$sendToUserID = $_GET['messageUser'] ?? false;
$sendToUsername = false;
if ($sendToUserID) $sendToUsername = users::getUserNameFromUid($sendToUserID);

$replyToID = $_GET['replyTo'] ?? false;
$subject = false;
$body = false;
if ($replyToID) {
   $replyToInfo = db::run("SELECT * FROM messages WHERE id = :id", [":id" => $replyToID])->fetch(PDO::FETCH_OBJ);
   if ($replyToInfo) {
      $subject = "RE: " . $replyToInfo->title;
      $sendToUserID = $replyToInfo->messageFrom == SESSION["userId"] ? $replyToInfo->messageTo : $replyToInfo->messageFrom;
      $sendToUsername = users::getUserNameFromUid($sendToUserID);
   }
}
?>
<?php if (!SESSION["messages"] || SESSION["ssc"]) { ?>
   <div class="alert alert-warning" role="alert">
      Your messages have been disabled. To send and recieve messages please enable messaging in <a href="/settings">Settings</a>
   </div>
<?php } ?>
<div class="row py-4 d-flex justify-content-center ">
   <div class="col-1">
      <a class="btn btn-dark" style="background-color: purple; border-color: purple;" href="/messages/" role="button">Back</a>
   </div>
   <div class="col-10">
      <div class="card w-75">
         <div class="card-header"><?=SITE_CONFIG["site"]["name"]?> Messages</div>
         <div class="card-body">
            <div class="input-group mb-3">
               <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1">To</span>
               </div>
               <input type="text" class="form-control" id="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" <?= $sendToUsername ? " value=\"$sendToUsername\" disabled" : "" ?>>
            </div>
            <div class="input-group mb-3">
               <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1">Subject</span>
               </div>
               <input type="text" class="form-control" id="title" aria-label="title" aria-describedby="basic-addon1" <?= $subject ? " value=\"$subject\" disabled" : "" ?>>
            </div>
            <div class="form-group">
               <label for="message">Message</label>
               <textarea class="form-control" id="message" placeholder="Markdown is supported" rows="3"></textarea>
            </div>
            <button type="button" data-control="sendMessage" class="btn btn-success" <?= SESSION["messages"] && !SESSION["ssc"] ? "" : "disabled" ?>><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> <i class="fas fa-paper-plane"></i> Send Message</button>
            <script>
               var simplemde = new SimpleMDE({
                  element: $("#message")[0],
                  autosave: {
                     enabled: true,
                     unique_id: "message<?= time() ?>",
                  },
                  forceSync: true,
               });

               $("[data-control='sendMessage']").on("click", this, function() {
                  var btn = this;
                  $(btn).attr("disabled", "disabled").find("span").show();
                  $.post('/api/messages/sendMessage', {
                     "username": $("#username").val(),
                     "title": $("#title").val(),
                     "message": $("#message").val()
                  }, function(data) {
                     if (data.success) {
                        toastr["success"](data.message);
                     } else {
                        toastr["error"](data.message);
                     }
                     $(btn).removeAttr("disabled").find("span").hide();
                  })
               })
            </script>
         </div>
      </div>
   </div>
   <?php pageBuilder::buildFooter(); ?>