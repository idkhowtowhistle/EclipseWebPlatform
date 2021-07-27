<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

$subForumId = $_GET["id"] ?? false;
if (!$subForumId) pageBuilder::errorCode(404);

$subForumInfo = forum::getSubForumInfo($subForumId);
if (!$subForumInfo) pageBuilder::errorCode(404);

$subForumPosts = forum::getSubForumThreads($subForumId);

pageBuilder::buildHeader();
?>
<nav class="breadcrumb"><a href="/" class="breadcrumb-item">Mercury</a> <a href="/forum/" class="breadcrumb-item">Forum Home</a><span class="breadcrumb-item active"><?= $subForumInfo->subName ?></span></nav>
    <?php if (!SESSION["ssc"] && $subForumInfo->isPostingAllowed || SESSION["adminLevel"] > 2) { ?>
        <a class="btn btn-primary mb-2" href="/forum/addPost?subforumId=<?= $subForumId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Thread</a>
    <?php } ?>
<table class="table mb-4">
    <thead class="table-bordered bg-light">
        <tr>
            <th class="font-weight-normal py-2 col-7" scope="col">Post title</th>
            <th class="font-weight-normal py-2" scope="col">Replies</th>
            <th class="font-weight-normal py-2" scope="col">Author</th>
            <th class="font-weight-normal py-2" scope="col"><span class="float-right">Last Active</span></th>
        </tr>
    </thead>
    <tbody class="bg-white border rounded">
        <?php while ($row = $subForumPosts->fetch(PDO::FETCH_OBJ)) {
            $userInfo = users::getUserInfoFromUid($row->authorId); ?>
            <tr>
                <td class="table-4-col-7">
                    <p class="my-0"><a href="/forum/viewPost?id=<?= $row->id ?>"><?= htmlspecialchars($row->postTitle) ?></a></p>
                </td>
                <td class="table-4-col-3"><?= forum::getReplyCount($row->id); ?></td>
                <td>
                    <p class="my-0"><a href="/user?id=<?= $row->authorId ?>" <?= $userInfo->admin > 2 ? "title='This user is an Administrator'" : "" ?> class="pl-1 <?= $userInfo->admin > 2 ? 'text-danger font-weight-bold' : "" ?> text-truncate"><?= $userInfo->admin > 2 ? '<i class="fas fa-shield-alt"></i>' : "" ?> <?= users::getUsernameFromUid($row->authorId) ?></a></p>
                </td>
                <td class="table-4-col-2 text-right">
                    <p class="my-0"><?= general::time_elapsed("@" . $row->bumpIndex, false, true) ?></p>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
    <?php if (!SESSION["ssc"] && $subForumInfo->isPostingAllowed || SESSION["adminLevel"] > 2) { ?>
        <a class="btn btn-primary mt-0" href="/forum/addPost?subforumId=<?= $subForumId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Thread</a>
    <?php } ?>
<?php pageBuilder::buildFooter(); ?>
