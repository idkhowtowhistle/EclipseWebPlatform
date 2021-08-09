<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();

if (!SESSION) redirect("/");

if (SESSION["adminLevel"] < 2) {
   redirect("/dashboard");
}
$query = $pdo->prepare("SELECT * FROM invitekeys WHERE NOT deleted ORDER BY id DESC");
$query->execute();
?>
<div class="app py-4 container">
   <h3 class="mb-4 font-weight-normal">Invite Keys</h3>
   <div class="row py-4">
      <div class="col">
         <div class="list-group" id="list-tab" role="tablist">
            <a aria-selected="true" aria-controls="admin" role="tab" href="#list-admin" data-toggle="list" id="list-admin-list" class="list-group-item list-group-item-action active">Create Key</a>
            <a class="list-group-item list-group-item-action" id="list-mod-list" data-toggle="list" href="#list-mod" role="tab" aria-controls="mod" aria-selected="false">Invite Keys</a>
         </div>
      </div>
      <div class="col-lg-10">
         <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade active show" id="list-admin" role="tabpanel" aria-labelledby="list-admin-list">
               <div class="row pb-4">
                  <div class="col-md-7">
                     <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="terms">
                        <label class="form-check-label" for="exampleCheck1">I agree that after creating this key the user I invite will follow the rules and TOS.</label>
                     </div>
                     <hr>
                     <button class="btn btn-success btn-lg btn-block" data-control="createKey" tabindex="4"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Create Key</button>
                     <script>
                        $('button[data-control="createKey"]').on('click', this, function() {
                           var button = this;
                           $(button).attr("disabled", "disabled").find("span").show();
                           $.post('/api/admin/createKey', {
                              "agree": $("#terms").is(":checked")
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
                  <div class="col-md-5">
                     <div class="card">
                        <div class="card-header bg-primary text-white">Invite Keys</div>
                        <div class="card-body">
                           <p class="mb-2">Invite Keys allow other users to join <?= SITE_CONFIG["site"]["name"] ?>. Please ask an administrator before creating invite keys</p>
                           <p class="mb-1">Some small rules you should take note of before creating a key:</p>
                           <li>Only make one at a time</li>
                           <li>Spreading keys without asking administrators will get you banned</li>
                           <li><b>Administrators can deactivate all keys</b></li>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="tab-pane fade" id="list-mod" role="tabpanel" aria-labelledby="list-mod-list">
               <div class="table-responsive">
                  <table id="keyTable" class="table table-striped">
                     <thead>
                        <tr>
                           <th scope="col">ID</th>
                           <th scope="col">Invite Key</th>
                           <th scope="col">Creator</th>
                           <th scope="col">Used</th>
                           <th scope="col">Used By</th>
                           <th scope="col">Date Created</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php while ($row = $query->fetch(PDO::FETCH_OBJ)) { ?>
                           <tr>
                              <th scope="row"><?= $row->id ?></th>
                              <td><?= $row->inviteKey ?></td>
                              <td><?= users::getUsernameFromUid($row->creatorId) ?></td>
                              <td><?= !$row->keyUsed ? "No" : "Yes" ?></td>
                              <td><?= !$row->usedById ? "N/A" : users::getUsernameFromUid($row->usedById) ?></td>
                              <td><?= date("n/j/Y - g:i A", $row->createdOn) ?></td>
                           </tr>
                        <?php } ?>
                     </tbody>
                     <script>
                        $(document).ready(function() {
                           //Pagination full
                           $('#keyTable').DataTable({
                              "pagingType": "simple_numbers",
                              "paging": true
                           });
                        });
                     </script>
                  </table>
               </div>
               <?php if (SESSION["adminLevel"] >= 3) { ?>
                  <div class="card">
                     <div class="card-body">
                        <div class="row my-1">
                           <div class="col-lg-2">
                              <input type="number" min="1" placeholder="Key ID" class="form-control" name="keyId" id="keyId" value="">
                           </div>
                           <div class="col-lg-2">
                              <button type="submit" id="update" name="update" data-control="updateKey" class="btn btn-danger btn-block"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Delete</button>
                           </div>
                           <script>
                              $('button[data-control="updateKey"]').on('click', this, function() {
                                 var button = this;
                                 $(button).attr("disabled", "disabled").find("span").show();
                                 $.post('/api/admin/updateKey', {
                                    "keyId": $("#keyId").val()
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
                     </div>
                  </div>
               <?php } ?>
            </div>
         </div>
      </div>
      <?php
      pageBuilder::buildFooter();
      ?>