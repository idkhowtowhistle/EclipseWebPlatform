<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

$threadId = $_GET["id"] ?? false;
$threadInfo = forum::getThreadInfo($threadId);
if (!$threadInfo) pageBuilder::errorCode(404);

$authorInfo = users::getUserInfoFromUid($threadInfo->authorId);

$replies = db::run(
    "SELECT subforum_replies.*, users.username, users.admin 
    FROM subforum_replies WHERE replyTo = :threadId ORDER BY datePosted ASC
    INNER JOIN users ON users.id = subforum_replies.authorId", 
    [":threadId" => $threadId]
);

pageBuilder::buildHeader();
?>
<nav class="breadcrumb">
    <a href="/" class="breadcrumb-item">Mercury</a> 
    <a href="/forum/" class="breadcrumb-item">Forum Home</a>
    <a href="/forum/" class="breadcrumb-item"><?=forum::getSubForumNameFromId($threadInfo->subForumId)?></a>
    <span class="breadcrumb-item active"><?=htmlspecialchars($threadInfo->postTitle)?></span>
</nav>
<?php if (($threadInfo->canReply && !SESSION["ssc"]) || SESSION["adminLevel"] > 2) { ?>
    <a class="btn btn-primary mb-2" href="/forum/addPost?postId=<?= $threadId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Thread</a>
<?php } ?>
<div class="card rounded-0 shadow-sm">
    <div class="card-header rounded-0 text-white bg-secondary">
        <?=htmlspecialchars($threadInfo->postTitle)?>
    </div>
    <div class="card-body">
        <div class="reply">
            <div class="col-2 divider-right">
                <a class="pl-1 <?= $authorInfo->admin > 2 ? 'text-danger font-weight-bold' : ""?> text-truncate" <?=$authorInfo->admin > 2 ? "title='This user is an Administrator'" : ""?> href="/user?id=<?=$authorInfo->id?>"><?=$authorInfo->admin > 2 ? '<i class="fas fa-shield-alt"></i>' : ""?> <?=$authorInfo->username?></a>
                <br>
                <img src="/thumbnails/avatar?id=<?=$threadInfo->authorId?>&amp;x=100&amp;y=100" title="<?=$authorInfo->username?>" alt="<?=$authorInfo->username?>" class="img-fluid">
                <br>
                <span>Total Posts: <?=users::getUserForumPosts($authorInfo->id)?></span>
            </div>
            <div class="col-6">
                <small class="text-muted">Posted on <?=date("n/j/Y", $threadInfo->datePosted)?> at <?=date("h:i:sa", $threadInfo->datePosted)?></small>
                <br>
                <?=htmlspecialchars($threadInfo->postContents)?>
            </div>
        </div>
    </div>
</div>
<br>
<?php while ($reply = $replies->fetch(PDO::FETCH_OBJ)) { ?>
<div class="card rounded-0 shadow-sm">
    <div class="card-body">
        <div class="reply">
            <div class="col-2 divider-right">
                <a class="pl-1 <?=$reply->admin > 2 ? 'text-danger font-weight-bold' : ""?> text-truncate" <?=$reply->admin > 2 ? "title='This user is an Administrator'" : ""?> href="/user?id=<?=$reply->authorId?>"><?=$reply->admin > 2 ? '<i class="fas fa-shield-alt"></i>' : ""?> <?=$reply->username?></a>
                <br>
                <img src="/thumbnails/avatar?id=<?=$reply->authorId?>&amp;x=100&amp;y=100" title="<?=$reply->username?>" alt="<?=$reply->username?>" class="img-fluid">
                <br>
                <span>Total Posts: <?=users::getUserForumPosts($reply->authorId)?></span>
            </div>
            <div class="col-6">
                <small class="text-muted">Posted on <?=date("n/j/Y", $reply->datePosted)?> at <?=date("h:i:sa", $reply->datePosted)?> - Replying to <?=htmlspecialchars($threadInfo->postTitle)?></small>
                <br>
                <?=htmlspecialchars($reply->replyContents)?>
            </div>
        </div>
    </div>
</div>
<?php } if (($threadInfo->canReply && !SESSION["ssc"]) || SESSION["adminLevel"] > 2) { ?>
    <a class="btn btn-primary mt-2" href="/forum/addPost?postId=<?= $threadId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Thread</a>
<?php } ?>
<?php pageBuilder::buildFooter(); ?>