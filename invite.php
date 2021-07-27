<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
users::requireLogin();

$userId = SESSION["userId"];
$query = $pdo->prepare("SELECT * FROM invitekeys WHERE NOT deleted AND creatorId = :uid ORDER BY id DESC");
$query->bindParam(":uid", $userId, PDO::PARAM_INT);
$query->execute();
?>

<div class="app py-4 container">
<?php if(SESSION["ssc"]) { ?>
<div class="alert alert-danger" role="alert">
  Your account has been restricted.
</div>
<?php } ?>
<h3 class="mb-4 font-weight-normal">Invite Keys</h3>
<div class="row py-4">
<div class="col-2">
   <div class="list-group" id="list-tab" role="tablist">
      <a aria-selected="true" aria-controls="admin" role="tab" href="#list-admin" data-toggle="list" id="list-admin-list" class="list-group-item list-group-item-action active">Create Key</a>
      <a class="list-group-item list-group-item-action" id="list-mod-list" data-toggle="list" href="#list-mod" role="tab" aria-controls="mod" aria-selected="false">Invite Keys</a>
   </div>
</div>
<div class="col-10">
<div class="tab-content" id="nav-tabContent">
   <div class="tab-pane fade active show" id="list-admin" role="tabpanel" aria-labelledby="list-admin-list">
      <div class="row pb-4">
         <div class="col-md-7">
            <div class="form-check">
               <input type="checkbox" class="form-check-input" id="terms">
               <label class="form-check-label" for="exampleCheck1">I agree that after creating this key the user I invite will follow the rules and TOS.</label>
            </div>
            <hr>
            <button class="btn btn-success btn-lg btn-block" data-control="createKey" tabindex="4" <?=SESSION["ssc"]?"disabled":""?>><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Create Key</button>
            <script> 
               $('button[data-control="createKey"]').on('click', this, function()
               {
               var button = this; 
               $(button).attr("disabled", "disabled").find("span").show();
               $.post('/api/account/createKey', {"agree":$("#terms").is(":checked")}, function(data)
               {
               if(data.success){ toastr["success"](data.message); }
               else{ toastr["error"](data.message); }
               $(button).removeAttr("disabled").find("span").hide();
               });
               });
            </script>
         </div>
         <div class="col-md-5">
            <div class="card">
               <div class="card-header bg-primary text-white">Invite Keys</div>
               <div class="card-body">
                  <p class="mb-2">Invite Keys allow other users to join <?=SITE_CONFIG["site"]["name"]?>. You can create a key every <code>2 months</code></p>
                  <p class="mb-1">Some small rules you should take note of before creating a key:</p>
                  <li>Make sure the user you invite will follow the rules</li>
                  <li>You cannot revoke an invite</li>
                  <li>All invites will be logged</li>
                  <li>If a user you invite breaks the rules your account will be <strong>deleted</strong></li>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="tab-pane fade" id="list-mod" role="tabpanel" aria-labelledby="list-mod-list">
      <table id="keyTable" class="table table-striped">
         <thead>
            <tr>
               <th scope="col">Invite Key</th>
               <th scope="col">Used</th>
               <th scope="col">Used By</th>
               <th scope="col">Date Created</th>
            </tr>
         </thead>
         <tbody>
         <?php while($row = $query->fetch(PDO::FETCH_OBJ)) { ?>
            <tr>
               <td><?=$row->inviteKey?></td>
               <td><?=!$row->keyUsed ? "No" : "Yes"?></td>
               <td><?=!$row->usedById ? "N/A" : users::getUsernameFromUid($row->usedById)?></td>
               <td><?=date("n/j/Y - g:i A", $row->createdOn)?></td>
            </tr>
            <?php } ?>
         </tbody>
         <script>
            $(document).ready(function () {
                //Pagination full
                $('#keyTable').DataTable({
                "pagingType": "simple_numbers",
             "paging": true
                });
            });
         </script>
      </table>
      </div>
   </div>
</div>

<?php pageBuilder::buildFooter(); ?>