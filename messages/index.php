<?php
   require $_SERVER["DOCUMENT_ROOT"]."/api/private/core.php";
   if(!SESSION) redirect("/");
   $userId = SESSION["userId"];
   pageBuilder::$pageConfig["title"] = "Messages";
   pageBuilder::buildHeader();

   $query = $pdo->prepare("SELECT * FROM messages WHERE messageTo = :userId ORDER BY messageDate ASC");
   $query->bindParam(":userId", $userId, PDO::PARAM_INT);
   $query->execute();

   
   $sent = $pdo->prepare("SELECT * FROM messages WHERE messageFrom = :userId ORDER BY messageDate ASC");
   $sent->bindParam(":userId", $userId, PDO::PARAM_INT);
   $sent->execute();

?> 
<?php if(!SESSION["messages"] || SESSION["ssc"]) { ?>
<div class="alert alert-warning" role="alert">
   Your messages have been disabled. To send and recieve messages please enable messaging in <a href="/settings">Settings</a>
</div>
<?php } ?>
<div class="row py-4">
<div class="col-2">
   <div class="list-group" id="list-tab" role="tablist">
      <a aria-selected="true" aria-controls="admin" role="tab" href="#list-inbox" data-toggle="list" id="list-inbox-list" class="list-group-item list-group-item-action active">Inbox</a>
      <a aria-selected="true" aria-controls="admin" role="tab" href="#list-sent" data-toggle="list" id="list-sent-list" class="list-group-item list-group-item-action">Sent</a>
   </div>
   <hr>

   <a class="btn btn-success btn-block <?= SESSION["messages"] && !SESSION["ssc"] ? "" : "disabled" ?>" href="/messages/new" role="button"><i class="fas fa-envelope-open-text"></i> New Message</a>
</div>
<div class="col-10">
   <div class="tab-content" id="nav-tabContent">
      <div class="tab-pane fade active show" id="list-inbox" role="tabpanel" aria-labelledby="list-inbox-list">
         <div class="row pb-4">
            <div class="card w-100 shadow-sm rounded-0">
               <div class="card-body">
                  <table class="table table-hover table-striped table-bordered" id="inboxTable">
                     <thead class="bg-light">
                        <tr>
                           <th class="font-weight-normal py-2" scope="col" >Title</th>
                           <th class="font-weight-normal py-2" scope="col" >User</th>
                           <th class="font-weight-normal py-2" scope="col" >Date</th>
                        </tr>
                     </thead>
                     <tbody>
                     <?php while($row = $query->fetch(PDO::FETCH_OBJ)) { ?>
                        <tr>
                           <td><a href="/messages/view?messageId=<?=$row->id?>"><?=$row->title?></a></td>
                           <td><a href="/user/<?=users::getUidFromUsername(users::getUsernameFromUid($row->messageFrom))?>"><?=users::getUsernameFromUid($row->messageFrom)?></a></td>
                           <td><?=date("F j, Y, g:i a", $row->messageDate)?></td>
                        </tr>
                     <?php } ?>
                     </tbody>
                  </table>
                  <script>
                  $(document).ready(function () {
                     $('#inboxTable').DataTable({
                        "pagingType": "numbers"
                     });
                     $('.dataTables_length').addClass('bs-select');
                  });
                  </script>
               </div>
            </div>
         </div>
      </div>
      <div class="tab-pane fade" id="list-sent" role="tabpanel" aria-labelledby="list-sent-list">
      <div class="row pb-4">
            <div class="card w-100 shadow-sm rounded-0">
               <div class="card-body">
                  <table class="table table-hover table-striped table-bordered" id="sentTable">
                     <thead class="bg-light">
                        <tr>
                           <th class="font-weight-normal py-2" scope="col" >Title</th>
                           <th class="font-weight-normal py-2" scope="col" >Sent To</th>
                           <th class="font-weight-normal py-2" scope="col" >Date</th>
                        </tr>
                     </thead>
                     <tbody>
                     <?php while($row = $sent->fetch(PDO::FETCH_OBJ)) { ?>
                        <tr>
                           <td><a href="/messages/view?messageId=<?=$row->id?>"><?=$row->title?></a></td>
                           <td><a href="/user/<?=users::getUidFromUsername(users::getUsernameFromUid($row->messageTo))?>"><?=users::getUsernameFromUid($row->messageTo)?></a></td>
                           <td><?=date("F j, Y, g:i a", $row->messageDate)?></td>
                        </tr>
                     <?php } ?>
                     </tbody>
                  </table>
                  <script>
                  $(document).ready(function () {
                     $('#sentTable').DataTable({
                        "pagingType": "numbers"
                     });
                     $('.dataTables_length').addClass('bs-select');
                  });
                  </script>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<?php
   pageBuilder::buildFooter();
?>
