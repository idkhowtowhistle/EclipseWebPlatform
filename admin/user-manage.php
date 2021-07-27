<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
if (SESSION["adminLevel"] < 2) pageBuilder::errorCode(404);
pageBuilder::buildHeader();
?>
<div class="row">
   <div class="col-sm">
      <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
         <a class="nav-link active" id="v-pills-ssc-tab" data-toggle="pill" href="#v-pills-ssc" role="tab" aria-controls="v-pills-home" aria-selected="true">Super Safe Chat</a>
         <a class="nav-link" id="v-pills-gb-tab" data-toggle="pill" href="#v-pills-gb" role="tab" aria-controls="v-pills-gb">Game Ban</a>
      </div>
   </div>
   <div class="col-lg-10">
      <div class="tab-content" id="v-pills-tabContent">
         <div class="tab-pane fade show active" id="v-pills-ssc" role="tabpanel" aria-labelledby="v-pills-ssc-tab">
            <div class="row">
               <div class="col">
                  <div class="card">
                     <div class="card-body">
                        <div class="form-group mb-3">
                           <label for="username">Username</label>
                           <input type="text" class="form-control" id="username" placeholder="Username">
                        </div>
                        <div class="form-group mb-3">
                           <label for="option">Option</label>
                           <select class="form-control" id="option">
                              <option value="1">Super Safe Chat</option>
                              <option value="0">Restore Chat</option>
                           </select>
                        </div>
                        <div class="form-group mb-3">
                           <label for="reason">Reason</label>
                           <textarea class="form-control" id="reason" rows="3"></textarea>
                        </div>
                        <button type="button" data-control="updateSSC" class="btn btn-lg btn-block btn-success"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Manage SSC</button>
                     </div>
                  </div>
                  <script>
                     $('button[data-control="updateSSC"]').on('click', this, function() {
                        var button = this;
                        $(button).attr("disabled", "disabled").find("span").show();
                        $.post('/api/admin/updateSSC', {
                           "username": $("#username").val(),
                           "option": $("#option").val(),
                           "reason": $("#reason").val()
                        }, function(data) {
                           if (data.success) {
                              toastr["success"](data.message);
                           } else {
                              toastr["error"](data.message);
                           }
                           $(button).removeAttr("disabled").find("span").hide();
                        });
                     });
                  </script>
               </div>
               <div class="col-5">
                  <div class="card border-info">
                     <div class="card-header">
                        Super Safe Chat
                     </div>
                     <div class="card-body">
                        Super Safe Chat is used to limit a users social abilities on-site if for example the user was under 13.<br>
                        Some rules to follow while using SSC:
                        <ul>
                           <li>
                              Ask an administrator before giving a user SSC
                           </li>
                           <li>
                              There must be sufficient grounds for giving a user SSC, using the reason box.
                           </li>
                           <li>
                              Abusing this system will get you banned.
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="tab-pane fade" id="v-pills-gb" role="tabpanel" aria-labelledby="v-pills-gb-tab">
            <div class="row">
               <div class="col">
                  <div class="card">
                     <div class="card-body">
                        <div class="form-group mb-3">
                           <label for="username-gb">Username</label>
                           <input type="text" class="form-control" id="username-gb" placeholder="Username">
                        </div>
                        <div class="form-group mb-3">
                           <label for="option-gb">Option</label>
                           <select class="form-control" id="option-gb">
                              <option value="1">Game Ban</option>
                              <option value="0">Unban</option>
                           </select>
                        </div>
                        <div class="form-group mb-3">
                           <label for="reason-gb">Reason</label>
                           <textarea class="form-control" id="reason-gb" rows="3"></textarea>
                        </div>
                        <button type="button" data-control="gameBan" class="btn btn-lg btn-block btn-success"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Manage Game Ban</button>
                     </div>
                  </div>
                  <script>
                     $('button[data-control="gameBan"]').on('click', this, function() {
                        var button = this;
                        $(button).attr("disabled", "disabled").find("span").show();
                        $.post('/api/admin/gameBan', {
                           "username": $("#username-gb").val(),
                           "option": $("#option-gb").val(),
                           "reason": $("#reason-gb").val()
                        }, function(data) {
                           if (data.success) {
                              toastr["success"](data.message);
                           } else {
                              toastr["error"](data.message);
                           }
                           $(button).removeAttr("disabled").find("span").hide();
                        });
                     });
                  </script>
               </div>
               <div class="col-5">
                  <div class="card border-info">
                     <div class="card-header">
                        Game Ban
                     </div>
                     <div class="card-body">
                        Game bans are used to prohibit users from hosting or joining servers.<br>
                        Some rules to follow while using game bans:
                        <ul>
                           <li>
                              Ask an administrator before giving a user a game ban
                           </li>
                           <li>
                              There must be sufficient grounds for giving a user a game  ban, using the reason box.
                           </li>
                           <li>
                              Abusing this system will get you banned.
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>