<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

$subforums = db::run("SELECT * FROM subforums ORDER BY subImportance DESC");

pageBuilder::$pageConfig["title"] = "Forum";
pageBuilder::buildHeader();
?>
<nav class="breadcrumb">
   <a href="/" class="breadcrumb-item"><?=SITE_CONFIG["site"]["name"]?></a> 
   <span class="breadcrumb-item active">Forum Home</span>
</nav>
<table class="table mb-4">
   <thead class="table-bordered bg-light">
      <tr>
         <th class="font-weight-normal py-2" scope="col">Category</th>
         <th class="font-weight-normal py-2" scope="col">Posts</th>
         <th class="font-weight-normal py-2" scope="col">Threads</th>
         <th class="font-weight-normal py-2 text-right" scope="col">Last Post</th>
      </tr>
   </thead>
   <tbody class="bg-white border rounded">
      <?php while ($subforum = $subforums->fetch(PDO::FETCH_OBJ)) { $thread = forum::getLastSubForumPost($subforum->id); ?>
         <tr>
            <td class="table-4-col-1">
               <p class="my-0"><a href="/forum/sub?id=<?=$subforum->id?>"><?=$subforum->subName?></a></p>
               <p class="my-0"><?=$subforum->subDesc?></p>
            </td>
            <td class="table-4-col-2"><?=forum::getPostsInSubForum($subforum->id)?></td>
            <td class="table-4-col-3"><?=forum::getThreadsInSubForum($subforum->id)?></td>
            <td class="table-4-col-4 text-right">
               <?php if ($thread) { ?>
               <p class="my-0"><a href="/forum/viewPost?id=<?=$thread->id?>"><?=general::filterText($thread->postTitle, true, false)?></a></p>
               <p class="my-0"><a href="/user/<?= $thread->authorId ?>" <?= $thread->admin > 2 ? "title='This user is an Administrator'" : "" ?> class="pl-1 <?= $thread->admin > 2 ? 'text-danger font-weight-bold' : "" ?> text-truncate"><?= $thread->admin > 2 ? '<i class="fas fa-shield-alt"></i>' : "" ?> <?=$thread->username?></a></p>
               <?php } else { ?>
               <p class="my-0 text-muted">No posts yet</p>
               <?php } ?>
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