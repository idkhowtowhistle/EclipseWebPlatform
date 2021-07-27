<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

if (isset($_GET["subforumId"])) {
    $postInfo = false;
    $subForumId = $_GET["subforumId"];
} elseif (isset($_GET["postId"])) {
    $postInfo = forum::getThreadInfo($_GET["postId"]);
    if (!$postInfo || $postInfo && $postInfo->deleted) {
        pageBuilder::errorCode(404);
    }
    $subForumId = $postInfo->subForumId;
} else {
    pageBuilder::errorCode(404);
}

$subForumInfo = forum::getSubForumInfo($subForumId);
if (!$subForumInfo) pageBuilder::errorCode(404);
if (SESSION["ssc"] || $postInfo && !$postInfo->canReply) pageBuilder::errorCode(404);
if (!$postInfo) {
    if (!$subForumInfo->isPostingAllowed) pageBuilder::errorCode(404);
}

$errors = ["subject" => false, "body" => false, "general" => false];
$subject = $body = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $body = $_POST["body"];
    $userId = SESSION["userId"];

    if (!$postInfo) {
        $subject = $_POST["subject"];
        if (!trim($subject)) {
            $errors["subject"] = "Subject cannot be empty";
        }
        if (strlen($subject) < 3 || strlen($subject) > 64) {
            $errors["subject"] = "Subject must be between 3 to 64 characters";
        }
        if(isset($_POST["replies"])) {
            $replies = $_POST["replies"] == "false" ? false : true;
        } else {
            $replies = false;
        }
    }
    if (!trim($body)) {
        $errors["body"] = "Body cannot be empty";
    }
    if (strlen($body) > 10000 || strlen($body) < 3) {
        $errors["body"] = "Body must be between 3 to 10000 characters";
    }

    $ratecheck = $pdo->prepare("SELECT (SELECT COUNT(*) FROM subforum_threads WHERE authorId = :uid AND datePosted+60 > UNIX_TIMESTAMP()) + (SELECT COUNT(*) FROM subforum_replies WHERE authorId = :uid AND datePosted+60 > UNIX_TIMESTAMP()) AS ratecheck");
    $ratecheck->bindParam("uid", $userId, PDO::PARAM_INT);
    $ratecheck->execute();

    if ($ratecheck->fetchColumn()) {
        $errors["general"] = "Please wait 1 minute before sending another forum post";
    }

    if (!$errors["subject"] && !$errors["body"] && !$errors["general"]) {
        if ($postInfo) {
            $query = $pdo->prepare("INSERT INTO subforum_replies (postContents, replyTo, authorId, datePosted) VALUES (:body, :threadId, :author, UNIX_TIMESTAMP())");
            $query->bindParam(":body", $body, PDO::PARAM_STR);
            $query->bindParam(":threadId", $postInfo->id, PDO::PARAM_INT);
            $query->bindParam(":author", $userId, PDO::PARAM_INT);
            $query->execute();

            $query = $pdo->prepare("UPDATE subforum_threads SET bumpIndex = UNIX_TIMESTAMP() WHERE id = :id");
            $query->bindParam(":id", $postInfo->id, PDO::PARAM_INT);
            $query->execute();
        } else {
            $query = $pdo->prepare("INSERT INTO subforum_threads (subForumId, postTitle, postContents,  authorId, datePosted, canReply, bumpIndex) VALUES (:subId, :subject, :body, :author, UNIX_TIMESTAMP(), :replies, UNIX_TIMESTAMP()); SELECT LAST_INSERT_ID();");
            $query->bindParam(":subject", $subject, PDO::PARAM_STR);
            $query->bindParam(":body", $body, PDO::PARAM_STR);
            $query->bindParam(":subId", $subForumId, PDO::PARAM_INT);
            $query->bindParam(":replies", $replies, PDO::PARAM_INT);
            $query->bindParam(":author", $userId, PDO::PARAM_INT);
            $query->execute();

            $query = $pdo->prepare("SELECT id FROM subforum_threads WHERE authorId = :id ORDER BY id DESC");
            $query->bindParam(":id", $userId, PDO::PARAM_INT);
            $query->execute();
        }
    }
}
pageBuilder::buildHeader();
?>
<nav class="breadcrumb">
    <a href="/" class="breadcrumb-item">Mercury</a> <a href="/forum/" class="breadcrumb-item">Forum Home</a>
    <a href="/forum/sub?id=<?= $subForumId ?>" class="breadcrumb-item"><?= $subForumInfo->subName ?></a>
    <span class="breadcrumb-item active">
        <?php if ($postInfo) { ?>
            New Reply
        <?php } else { ?>
            New Post
        <?php } ?>
    </span>
</nav>
<h2 class="font-weight-normal">New <?= $postInfo ? 'Reply' : 'Thread' ?></h2>
<div class="row">
    <div class="col-7">
        <form method="post">
            <div class="form-group">
                <?php if (!$postInfo) { ?>
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control<?= $errors["subject"] ? ' is-invalid' : '' ?>" id="subject" name="subject" placeholder="64 characters max" value="<?= $subject ?>" required>
                    <div class="invalid-feedback">
                        <?= $errors["subject"] ?>
                    </div>
                <?php } else { ?>
                    <p>Replying to: <?= $postInfo->postContents ?></p>
                <?php } ?>
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea class="form-control<?= $errors["body"] ? ' is-invalid' : '' ?>" name="body" placeholder="3-10,000 characters max" id="body" rows="3"></textarea>
                <div class="invalid-feedback">
                    <?= $errors["body"] ?>
                </div>
            </div>
            <?php if (!$postInfo) { ?>
                <div class="form-group form-check">
                    <input type="checkbox" name="replies" class="form-check-input" id="replies">
                    <label class="form-check-label" for="replies">Allow replies</label>
                </div>
            <?php } ?>
            <button type="button" class="btn btn-danger" onclick="window.history.back();"><i class="fas fa-chevron-left"></i> Back</button>
            <button type="" class="btn btn-primary"><i class="fas fa-location-arrow"></i> Post <?= $postInfo ? "Reply" : "Thread" ?></button>
            <span class="text-danger float-right mr-2 mt-2"><?= $errors["general"] ?></span>
        </form>
    </div>
    <div class="col-5">
        <div class="card">
            <div class="card-header">
                Mercury Forum Rules
            </div>
            <div class="card-body">
                Make sure you post respectfully and follow the Site Rules when posting.
            </div>
        </div>
    </div>
</div>
<?php pageBuilder::buildFooter(); ?>