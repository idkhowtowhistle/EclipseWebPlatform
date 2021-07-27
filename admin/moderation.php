<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if (!SESSION || SESSION["adminLevel"] < 2) pageBuilder::errorCode(404);
pageBuilder::buildHeader();

$markdown = new Parsedown();
$markdown->setMarkupEscaped(true);
$markdown->setBreaksEnabled(true);
$markdown->setSafeMode(true);
$markdown->setUrlsLinked(true);
?>
<h3 class="font-weight-normal">User Moderation</h3>
<div class="row">
   <div class="col-6">
      <hr>
      <div class="form-group">
         <label for="username">Username</label>
         <input type="text" class="form-control" id="username">
      </div>
      <div class="form-group">
         <label for="type">Type</label>
         <select class="form-control" id="type">
            <option value="1">Warning</option>
            <option value="2">Ban</option>
            <option value="3">Permanent Ban</option>
            <option value="4">Unban</option>
         </select>
      </div>
      <div class="form-group" data-control="reason">
         <label for="reason">Reason</label>
         <textarea class="form-control" id="reason" rows="3">512 characters max - Markdown is supported</textarea>
      </div>
      <div class="form-group" data-control="date" style="display:none">
         <label for="date">Date</label>
         <input type="text" class="form-control" id="date" placeholder="MM/DD/YYYY">
      </div>
      <div class="btn-group btn-block" role="group" aria-label="Basic example">
         <button type="button" data-control="moderateUser" class="btn btn-danger"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Moderate</button>
         <button type="button" data-control="previewModeration" class="btn btn-primary"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Preview</button>
      </div>
      <script>
         $('button[data-control$="previewModeration"]').on('click', this, function() {
            var button = this;
            $(button).attr("disabled", "disabled").find("span").show();
            $.post('/api/admin/previewModeration', {
               "type": $("#type").val(),
               "reason": $("#reason").val(),
               "date": $("#date").val()
            }, function(data) {
               if (data.success) {
                  $(".preview").empty();
                  $(".preview").html(data.message);
                  toastr["success"]("Updated moderation preview");
               } else {
                  toastr["error"](data.message);
               }
               $(button).removeAttr("disabled").find("span").hide();
            });
         });

         $('button[data-control$="moderateUser"]').on('click', this, function() {
            var button = this;
            $(button).attr("disabled", "disabled").find("span").show();
            $.post('/api/admin/moderateUser', {
               "username": $("#username").val(),
               "type": $("#type").val(),
               "reason": $("#reason").val(),
               "date": $("#date").val()
            }, function(data) {
               if (data.success) {
                  toastr["success"](data.message);
               } else {
                  toastr["error"](data.message);
               }
               $(button).removeAttr("disabled").find("span").hide();
            });
         });

         $('#type').on('change', this, function() {
            if ($(this).val() == 2) {
               $('[data-control$="date"]').show(400);
            } else {
               $('[data-control$="date"]').hide(400);
            }

            if ($(this).val() == 4) {
               $('[data-control$="reason"]').hide(400);
               $('button[data-control$="previewModeration"]').attr("disabled", "disabled");
            } else {
               $('[data-control$="reason"]').show(400);
               $('button[data-control$="previewModeration"]').removeAttr("disabled");
            }
         });
      </script>
   </div>
   <div class="col-6">
      <h3 class="font-weight-normal">Preview</h3>
      <div class="preview">
         <div class="card">
            <div class="card-header alert-danger"><i class="fas fa-gavel"></i> <?=SITE_CONFIG["site"]["name"]?> Moderation</div>
            <div class="card-body">
               <h4 class="font-weight-normal mb-1">Account Terminated</h4>
               <p class="mb-1">Your account has been terminated for violating the <a href="/rules">Rules</a>.</p>
               <b>Done at: </b><?= date('j/n/Y g:i:s A \G\M\T') ?>
               <hr class="my-2">
               <label for="banreason" class="form-label mb-0">Reason:</label>
               <br>
               <code id="banreason">Generate a preview at anytime to see what will come up on a banned users screen</code>
               <hr class="mb-2">
               <p class="mb-0">You will not be able to reactivate your account.</p>
            </div>
         </div>
      </div>
   </div>
</div>

<?php pageBuilder::buildFooter() ?>