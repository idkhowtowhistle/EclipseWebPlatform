<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

$threadId = $_GET["id"] ?? false;
$threadInfo = forum::getThreadInfo($threadId);
if (!$threadInfo) pageBuilder::errorCode(404);

$replies = forum::getThreadReplies($threadId);

pageBuilder::$pageConfig["og:description"] = general::filterText($threadInfo->postContents, true, false);
pageBuilder::$pageConfig["title"] = general::filterText($threadInfo->postTitle, true, false);
pageBuilder::buildHeader();
?>
<nav class="breadcrumb">
    <a href="/" class="breadcrumb-item">Mercury</a>
    <a href="/forum/" class="breadcrumb-item">Forum Home</a>
    <a href="/forum/sub?id=<?= $threadInfo->subForumId ?>" class="breadcrumb-item"><?= forum::getSubForumNameFromId($threadInfo->subForumId) ?></a>
    <span class="breadcrumb-item active"><?= htmlspecialchars($threadInfo->postTitle) ?></span>
</nav>
<?php if (($threadInfo->canReply && !SESSION["ssc"] && !$threadInfo->locked) || SESSION["adminLevel"] > 2) { ?>
    <a class="btn btn-primary mb-2" href="/forum/addPost?postId=<?= $threadId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Post</a>
<?php } ?>
<div class="card rounded-0 shadow-sm">
    <div class="card-header rounded-0 text-white bg-secondary">
        <div class="row">
            <div class="col-6">
                <p class="my-auto d-inline">
                    <?= htmlspecialchars($threadInfo->postTitle) ?>
                    <br>
                    <small>Posted by <?= users::getUsernameFromUid($threadInfo->authorId) ?></small>
                </p>
            </div>
            <div class="col-6 mt-2">
                <?php if (SESSION["adminLevel"] > 1) { ?>
                    <div class="d-inline float-right">
                        <button type="button" data-locked="<?= $threadInfo->locked ? "true" : "false" ?>" data-control="lockPost" class="btn btn-danger"><i class="fas fa-lock<?= $threadInfo->locked ? "-open" : "" ?>"></i> <?= $threadInfo->locked ? "Unl" : "L" ?>ock Post</button>
                        <button type="button" data-pinned="<?= $threadInfo->pinned ? "true" : "false" ?>" data-control="pinPost" class="btn btn-success"><i class="fas fa-thumbtack"></i> <?= $threadInfo->pinned ? "Unp" : "P" ?>in Post</button>
                    </div>
                    <script>
                        $('button[data-control="lockPost"]').on('click', this, function() {
                            var button = this;
                            $(button).attr("disabled", "disabled");
                            $.post('/api/forum/lockPost', {
                                "postId": "<?= $threadId ?>",
                                "locked": $(this).attr("data-locked")
                            }, function(data) {
                                if (data.success) {
                                    location.reload();
                                } else {
                                    toastr["error"](data.message);
                                }
                                $(button).removeAttr("disabled");
                            });
                        });
                    </script>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="reply row">
            <div class="col-2 divider-right">
                <a class="pl-1 <?= $threadInfo->admin > 2 ? 'text-danger font-weight-bold' : "" ?> text-truncate" <?= $threadInfo->admin > 2 ? "title='This user is an Administrator'" : "" ?> href="/user?id=<?= $threadInfo->authorId ?>"><?= $threadInfo->admin > 2 ? '<i class="fas fa-shield-alt"></i>' : "" ?> <?= $threadInfo->username ?></a>
                <br>
                <img src="/thumbnails/avatar?id=<?= $threadInfo->authorId ?>&amp;x=100&amp;y=100" title="<?= $threadInfo->username ?>" alt="<?= $threadInfo->username ?>" class="img-fluid">
                <br>
                <span>Total Posts: <?= users::getUserForumPosts($threadInfo->authorId) ?></span>
            </div>
            <div class="col-6">
                <small class="text-muted">Posted on <?= date("n/j/Y", $threadInfo->datePosted) ?> at <?= date("h:i:sa", $threadInfo->datePosted) ?></small>
                <br>
                <?= general::filterText($threadInfo->postContents, true, false) ?>
            </div>
        </div>
    </div>
</div>
<br>
<?php while ($reply = $replies->fetch(PDO::FETCH_OBJ)) { ?>
    <div class="card rounded-0 shadow-sm">
        <div class="card-body">
            <div class="reply row">
                <div class="col-2 divider-right">
                    <a class="pl-1 <?= $reply->admin > 2 ? 'text-danger font-weight-bold' : "" ?> text-truncate" <?= $reply->admin > 2 ? "title='This user is an Administrator'" : "" ?> href="/user?id=<?= $reply->authorId ?>"><?= $reply->admin > 2 ? '<i class="fas fa-shield-alt"></i>' : "" ?> <?= $reply->username ?></a>
                    <br>
                    <img src="/thumbnails/avatar?id=<?= $reply->authorId ?>&amp;x=100&amp;y=100" title="<?= $reply->username ?>" alt="<?= $reply->username ?>" class="img-fluid">
                    <br>
                    <span>Total Posts: <?= users::getUserForumPosts($reply->authorId) ?></span>
                </div>
                <div class="col-6">
                    <small class="text-muted">Posted on <?= date("n/j/Y", $reply->datePosted) ?> at <?= date("h:i:sa", $reply->datePosted) ?></small>
                    <br>
                    <?= general::filterText($reply->replyContents, true, false) ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<div class="row">
    <div class="col">
        <?php if (($threadInfo->canReply && !SESSION["ssc"] && !$threadInfo->locked) || SESSION["adminLevel"] > 2) { ?>
            <a class="btn btn-primary mt-2" href="/forum/addPost?postId=<?= $threadId ?>" role="button"><i class="fab fa-telegram-plane"></i> Add Post</a>
        <?php } ?>
    </div>
    <div class="col mt-2">
        <nav>
            <ul class="pagination justify-content-end mb-0">
                <li class="page-item">
                    <a class="page-link" aria-label="Previous">
                        <span aria-hidden="true">«</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
                <li class="page-item"><a class="page-link">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">»</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<?php pageBuilder::buildFooter(); ?>