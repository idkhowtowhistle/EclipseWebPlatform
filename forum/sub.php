<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

$subForumId = $_GET["id"] ?? false;
$subForumInfo = forum::getSubForumInfo($subForumId);
if (!$subForumInfo) pageBuilder::errorCode(404);

$threads = forum::getSubForumThreads($subForumId);

pageBuilder::buildHeader();
?>
<nav class="breadcrumb">
    <a href="/" class="breadcrumb-item">Mercury</a>
    <a href="/forum/" class="breadcrumb-item">Forum Home</a>
    <span class="breadcrumb-item active"><?= $subForumInfo->subName ?></span>
</nav>
<?php if (SESSION && !SESSION["ssc"] && $subForumInfo->isPostingAllowed || SESSION && SESSION["adminLevel"] > 2) { ?>
    <a class="btn btn-primary mb-2" href="/forum/addPost?subforumId=<?= $subForumId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Thread</a>
<?php } ?>
<table class="table mb-4" id="forumThreads">
    <thead class="table-bordered bg-light">
        <tr>
            <th class="font-weight-normal py-2 col-7" scope="col">Post title</th>
            <th class="font-weight-normal py-2" scope="col">Replies</th>
            <th class="font-weight-normal py-2" scope="col">Author</th>
            <th class="font-weight-normal py-2 text-right" scope="col">Last Active</th>
        </tr>
    </thead>
    <tbody class="bg-white border rounded">
        <?php while ($thread = $threads->fetch(PDO::FETCH_OBJ)) { ?>
            <tr>
                <td class="table-4-col-7">
                    <p class="my-0"><?= $thread->locked ? '<i class="fas fa-lock text-danger font-weight-bold"></i>' : '' ?> <a href="/forum/viewPost?id=<?= $thread->id ?>"><?= general::filterText($thread->postTitle, true, false) ?></a></p>
                </td>
                <td class="table-4-col-3"><?= forum::getReplyCount($thread->id) ?></td>
                <td>
                    <p class="my-0"><a href="/user/<?= $thread->authorId ?>" <?= $thread->admin > 2 ? "title='This user is an Administrator'" : "" ?> class="pl-1 <?= $thread->admin > 2 ? 'text-danger font-weight-bold' : "" ?> text-truncate"><?= $thread->admin > 2 ? '<i class="fas fa-shield-alt"></i>' : "" ?> <?= $thread->username ?></a></p>
                </td>
                <td class="table-4-col-2 text-right">
                    <p class="my-0"><?= general::time_elapsed("@" . $thread->bumpIndex, false, true) ?></p>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<script>
        $(document).ready(function() {
            //Pagination full
            $('#forumThreads').DataTable({
                "pagingType": "simple_numbers",
                "paging": true,
                "sorting": false
            });
        });
</script>
<?php pageBuilder::buildFooter(); ?>