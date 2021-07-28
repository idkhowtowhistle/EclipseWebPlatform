<?php 
$bypassModeration = true;
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php'; 
if(!SESSION){ pageBuilder::errorCode(404); }

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reactivate"]) && users::undoUserModeration(SESSION["userId"]))
{ 
	die(header("Location: /"));
}

$moderationInfo = users::getUserModeration(SESSION["userId"]);
if(!$moderationInfo){ pageBuilder::errorCode(404); }

$markdown = new Parsedown();
$markdown->setMarkupEscaped(true);
$markdown->setBreaksEnabled(true);
$markdown->setSafeMode(true);
$markdown->setUrlsLinked(true);

$text = [
    "title" =>
    [
        1 => "Warning",
        2 => "Banned for ".general::time_elapsed("@".((($moderationInfo->timeEnds-$moderationInfo->timeStarted)+time())+1), false, false),
        3 => "Account Terminated"
    ],
    "header" =>
    [
        1 => "Please remember to follow the rules and terms of services",
        2 => "Your account has been banned for breaking the rules",
        3 => "Your account has been permanently deleted for violating the rules"
    ],
    "footer" =>
    [
        1 => "<a href='rules'>Rules</a> and <a href='tos'>Terms Of Services</a>",
        2 => "Your ban end". ($moderationInfo->timeEnds > time() ? "s":"ed"). "at " .date('j/n/Y g:i:s A \G\M\T', $moderationInfo->timeEnds).($moderationInfo->timeEnds > time() ? ", or in ".general::time_elapsed("@".($moderationInfo->timeEnds+1), true, false)." <br><br> Circumventing your ban on an alternate account while it is active may cause your ban time to be extended" : ""),
        3 => "We do not take appeals."
    ]
];

pageBuilder::$pageConfig["title"] = SITE_CONFIG["site"]["name"]." Moderation";
pageBuilder::buildHeader();
?>

<div class="card w-75 mx-auto">
   <div class="card-header alert-danger"><i class="fas fa-gavel"></i> <?=SITE_CONFIG["site"]["name"]?> Moderation</div>
   <div class="card-body">
      <h4 class="font-weight-normal mb-1"><?=$text["title"][$moderationInfo->type]?></h4>
      <p class="mb-1"><?=$text["header"][$moderationInfo->type]?></p>
      <b>Done at: </b><?=date('j/n/Y g:i:s A \G\M\T', $moderationInfo->timeStarted)?></b>
      <hr class="my-2">
      <label for="banreason" class="form-label mb-0">Reason:</label>
      <br>
      <code id="banreason"><?=$markdown->text($moderationInfo->reason, true)?></code>
      <hr class="mb-2">
      <p class="mb-0"><?=$text["footer"][$moderationInfo->type]?></p>
      <hr class="md-2">
      <?php if($moderationInfo->type == 1 || $moderationInfo->type == 2 && $moderationInfo->timeEnds < time()) { ?>
	<form method="post">
		<button name="reactivate" type="submit" class="btn btn-primary">Reactivate Account</button>
	</form>
	<?php } ?>
   </div>
</div>

<?php pageBuilder::buildFooter(); ?>