<?php
   require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";
   if(!SESSION) redirect("/");

   if(!isset($_GET["messageId"])){pageBuilder::errorCode(404);}

   $id = $_GET["messageId"];

   if(!$id){pageBuilder::errorCode(404);}

  $msgInfo = messages::getMessageInfo($id);
 
 if(!in_array(SESSION["userId"], [$msgInfo->messageFrom, $msgInfo->messageTo])) {
     pageBuilder::errorCode(404);
 }

 if($msgInfo->messageTo === SESSION["userId"]) {
    db::run("UPDATE messages SET sentToStatus = 1 WHERE id = :id", [":id" => $id]);
 }

 if($msgInfo->messageFrom === SESSION["userId"]) {
   db::run("UPDATE messages SET senderStatus = 1 WHERE id = :id", [":id" => $id]);
}

 $markdown = new Parsedown();
 $markdown->setMarkupEscaped(true);
 $markdown->setBreaksEnabled(true);
 $markdown->setSafeMode(true);
 $markdown->setUrlsLinked(true);

   pageBuilder::buildHeader();
?>
<?php if(!SESSION["messages"] || SESSION["ssc"]) { ?>
<div class="alert alert-warning" role="alert">
   Your messages have been disabled. To send and recieve messages please enable messaging in <a href="/settings">Settings</a>
</div>
<?php } ?>
<div class="row py-4 d-flex justify-content-center ">
<div class="col">
   <a class="btn btn-dark" style="background-color: purple; border-color: purple;" href="/messages/" role="button">Back</a> 
   <hr>
   <a class="btn btn-block btn-info <?= SESSION["messages"] && !SESSION["ssc"] ? "" : "disabled" ?>" href="/messages/new?replyTo=<?=$msgInfo->id?>" role="button"><i class="fas fa-reply"></i> Reply</a>
</div>
<div class="col-10">
   <div class="card w-75">
      <div class="card-header">
         <h4 class="font-weight-normal"><?=$msgInfo->title?></h4>
      </div>
      <div class="card-body">
         <div class="input-group mb-3">
            <div class="input-group-prepend">
               <span class="input-group-text" id="basic-addon1">From:</span> 
            </div>
            <input type="text" class="form-control" id="username" placeholder="<?=users::getUsernameFromUid($msgInfo->messageFrom)?>" aria-label="Username" aria-describedby="basic-addon1" disabled>
         </div>
         <div class="input-group mb-3">
            <div class="input-group-prepend">
               <span class="input-group-text" id="basic-addon1">To:</span>
            </div>
            <input type="text" class="form-control" id="username" placeholder="<?=users::getUsernameFromUid($msgInfo->messageTo)?>" aria-label="Username" aria-describedby="basic-addon1" disabled>
         </div>
        <p>Message:</p>
        <div class="message">
        <?=$markdown->text($msgInfo->messageContent)?>
        </div>
      </div>
      <div class="card-footer">
         <?="Message was sent at " . date("F j, Y, g:i a", $msgInfo->messageDate)?>
      </div>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>