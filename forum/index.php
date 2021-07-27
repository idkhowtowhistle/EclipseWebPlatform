<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
pageBuilder::buildHeader();

$query = $pdo->prepare("SELECT * FROM subforums ORDER BY subImportance DESC");
$query->execute();
?>
<nav class="breadcrumb"><a href="/" class="breadcrumb-item">Mercury</a> <span class="breadcrumb-item active">Forum Home</span></nav>
<table class="table mb-4">
   <thead class="table-bordered bg-light">
      <tr>
         <th class="font-weight-normal py-2" scope="col">Category</th>
         <th class="font-weight-normal py-2" scope="col">Posts</th>
         <th class="font-weight-normal py-2" scope="col">Threads</th>
         <th class="font-weight-normal py-2" scope="col"><span class="float-right">Last Post</span></th>
      </tr>
   </thead>
   <tbody class="bg-white border rounded">
      <?php while ($row = $query->fetch(PDO::FETCH_OBJ)) { ?>
         <tr>
            <td class="table-4-col-1">
               <p class="my-0"><a href="/forum/sub?id=<?=$row->id?>"><?= $row->subName ?></a></p>
               <p class="my-0"><?= $row->subDesc ?></p>
            </td>
            <td class="table-4-col-2"><?=forum::getPostsInSubForum($row->id)?></td>
            <td class="table-4-col-3"><?=forum::getThreadsInSubForum($row->id)?></td>
            <td class="table-4-col-4 text-right">
               <p class="my-0"><a href="#" title="remember our discord exists">Welcome to Mercury!</a></p>
               <p class="my-0"><a href="/user?id=2" title="This user is an Administrator" class="pl-1 text-danger text-truncate font-weight-bold"><i class="fas fa-shield-alt"></i> Multako</a></p>
            </td>
         </tr>
      <?php } ?>
   </tbody>
</table>
<div class="card">
   <div class="card-header bg-primary text-white">
      Statistics
   </div>
   <div class="card-body">
      <p><b>Users Online: </b> <?= users::getUsersOnline(); ?></p>
      <p class="mb-0"><b>Forum Posts:</b> <?=forum::getTotalPosts()?></p>
   </div>
</div>

<?php pageBuilder::buildFooter(); ?>