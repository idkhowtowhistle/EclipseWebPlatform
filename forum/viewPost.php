<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
pageBuilder::buildHeader();
$threadId = $_GET["id"] ?? false;
if (!$threadId) pageBuilder::errorCode(404);

$threadInfo = forum::getThreadInfo($threadId);
if (!$threadInfo) pageBuilder::errorCode(404);

$userInfo = users::getUserInfoFromUid($threadInfo->authorId);
?>
<nav class="breadcrumb"><a href="/" class="breadcrumb-item">Mercury</a> <a href="/forum/" class="breadcrumb-item">Forum Home</a><a href="/forum/" class="breadcrumb-item"><?= forum::getSubForumNameFromId($threadInfo->subForumId) ?></a><span class="breadcrumb-item active"><?= htmlspecialchars($threadInfo->postTitle) ?></span></nav>
<?php if (!SESSION["ssc"] && $threadInfo->canReply || SESSION["adminLevel"] > 2) { ?>
        <a class="btn btn-primary mb-2" href="/forum/addPost?postId=<?= $threadId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Thread</a>
<?php } ?>
<div class="card rounded-0 shadow-sm">
    <div class="card-header rounded-0 text-white bg-secondary">
        <?= htmlspecialchars($threadInfo->postTitle) ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-2 divider-right">
            <a class="pl-1 <?= $userInfo->admin > 2 ? 'text-danger font-weight-bold' : "" ?> text-truncate" <?= $userInfo->admin > 2 ? "title='This user is an Administrator'" : "" ?> href="/user?id=<?=$userInfo->id?>"><?= $userInfo->admin > 2 ? '<i class="fas fa-shield-alt"></i>' : "" ?> <?=$userInfo->username?></a>
            <br>
            <img src="/thumbnails/avatar?id=<?=$threadInfo->authorId?>&amp;x=100&amp;y=100" title="<?=$userInfo->username?>" alt="<?=$userInfo->username?>" class="img-fluid">
            <br>
            <span>Total Posts: <?=users::getUserForumPosts($userInfo->id)?></span>
        </div>
            <div class="col-6">
            <small class="text-muted">Posted on <?=date("n/j/Y", $threadInfo->datePosted)?> at <?=date("h:i:sa", $threadInfo->datePosted)?></small>
            <br>
            <?=htmlspecialchars($threadInfo->postContents)?>
            </div>
        </div>
    </div>
</div>
<?php if (!SESSION["ssc"] && $threadInfo->canReply || SESSION["adminLevel"] > 2) { ?>
        <a class="btn btn-primary mb-2" href="/forum/addPost?subforumId=<?= $threadId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Thread</a>
<?php } ?>
<?php pageBuilder::buildFooter(); ?>