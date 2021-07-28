<?php
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';
header("content-type: application/json");

if($_SERVER['REQUEST_METHOD'] != 'POST'){ api::respond(405, false, "Method Not Allowed"); }
api::requireLogin();
api::lastAction();
if(!SESSION["2fa"]) api::respond(400, false, "You need 2FA to do any admin action.");
if(SESSION["adminLevel"] < 2){ api::respond(400, false, "Not an administrator"); }
if(!isset($_POST["date"]) || !isset($_POST["reason"]) || !isset($_POST["type"])){ api::respond(400, false, "Invalid Request"); }
if($_POST["type"] < 1 || $_POST["type"] > 3){ api::respond(400, false, "Invalid Request"); }
if(!trim(htmlspecialchars($_POST["reason"]))){ api::respond(400, false, "You must supply a reason"); }
if($_POST["type"] == 2 && !trim($_POST["date"])){ api::respond(400, false, "Ban time not set"); }

$type = $_POST["type"];
$date = strtotime($_POST["date"]. " " . date("G:i:s"));
if($date < strtotime('tomorrow') && $type === 2){ api::respond(400, false, "Ban time must be at least 1 day long"); }

$markdown = new Parsedown();
$markdown->setMarkupEscaped(true);
$markdown->setBreaksEnabled(true);
$markdown->setSafeMode(true);
$markdown->setUrlsLinked(true);

$text = [
    "title" =>
    [
        1 => "Warning",
        2 => "Banned for ".general::time_elapsed("@".($date+1), false, false),
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
        2 => "Your ban ends at ".date('j/n/Y g:i:s A \G\M\T', $date).", or in ".general::time_elapsed("@".($date+1), true, false)." <br><br> Circumventing your ban on an alternate account while it is active may cause your ban time to be extended",
        3 => "We do not take appeals."
    ]

];

ob_start(); ?>
<div class="card">
            <div class="card-header alert-danger"><i class="fas fa-gavel"></i> <?=SITE_CONFIG["site"]["name"]?> Moderation</div>
            <div class="card-body">
               <h4 class="font-weight-normal mb-1"><?=$text["title"][$type]?></h4>
               <p class="mb-1"><?=$text["header"][$type]?></p>
               <b>Done at: </b><?=date('j/n/Y g:i:s A \G\M\T')?>
               <hr class="my-2">
               <label for="banreason" class="form-label mb-0">Reason:</label>
               <br>
               <code id="banreason"><?=$markdown->text(trim($_POST["reason"]), true)?></code>
               <hr class="mb-2">
               <p class="mb-0"><?=$text["footer"][$type]?></p>
               <hr class="mb-2">
               <?php if($type == 1) { ?>
               <a href="#" class="btn btn-primary px-4 disabled">Reactivate</a>
               <?php }?>
            </div>
         </div>
 <?php api::respond(200, true, ob_get_clean());