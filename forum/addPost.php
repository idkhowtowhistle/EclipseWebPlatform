<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

if (isset($_GET["subforumId"])) 
{
    $postInfo = false;
    $subForumId = $_GET["subforumId"] ?? false;
} 
elseif (isset($_GET["postId"])) 
{
    $postInfo = forum::getThreadInfo($_GET["postId"] ?? false);
    if (!$postInfo || $postInfo->deleted) pageBuilder::errorCode(404);
    $subForumId = $postInfo->subForumId;
} 
else 
{
    pageBuilder::errorCode(404);
}

$subForumInfo = forum::getSubForumInfo($subForumId);

if (!$subForumInfo) pageBuilder::errorCode(404);
if ($postInfo && !$postInfo->canReply && SESSION["adminLevel"] < 2 || SESSION["ssc"] || $postInfo && $postInfo->locked && SESSION["adminLevel"] < 2) pageBuilder::errorCode(404);
if (!$postInfo && SESSION["adminLevel"] < 2 && !$subForumInfo->isPostingAllowed) pageBuilder::errorCode(404);

$errors = ["subject" => false, "body" => false, "general" => false];
$subject = $body = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") 
{
    $body = $_POST["body"];
    $userId = SESSION["userId"];

    if (!$postInfo) 
    {
        $subject = $_POST["subject"];
        if (!strlen($subject)) $errors["subject"] = "Subject cannot be empty";
        else if (strlen($subject) < 3 || strlen($subject) > 64) $errors["subject"] = "Subject must be between 3 to 64 characters";
        $replies = (int)isset($_POST["replies"]);
    }

    if (!strlen($body)) $errors["body"] = "Body cannot be empty";
    else if (strlen($body) > 10000 || strlen($body) < 3) $errors["body"] = "Body must be between 3 to 10000 characters";

    $ratecheck = $pdo->prepare("SELECT (SELECT COUNT(*) FROM subforum_threads WHERE authorId = :uid AND datePosted+30 > UNIX_TIMESTAMP()) + (SELECT COUNT(*) FROM subforum_replies WHERE authorId = :uid AND datePosted+30 > UNIX_TIMESTAMP()) AS ratecheck");
    $ratecheck->bindParam("uid", $userId, PDO::PARAM_INT);
    $ratecheck->execute();

    if ($ratecheck->fetchColumn()) $errors["general"] = "Please wait at most 30 seconds before sending another forum post";

    if (!$errors["subject"] && !$errors["body"] && !$errors["general"]) 
    {
        if ($postInfo) 
        {
            db::run(
                "INSERT INTO subforum_replies (replyContents, replyTo, subForumId, authorId, datePosted) VALUES (:body, :threadId, :subId, :author, UNIX_TIMESTAMP())",
                [":body" => $body, ":threadId" => $postInfo->id, ":subId" => $subForumId, ":author" => $userId]
            );

            db::run("UPDATE subforum_threads SET bumpIndex = UNIX_TIMESTAMP() WHERE id = :id", [":id" => $postInfo->id]);

            header("Location: /forum/viewPost?id=" . $postInfo->id);
        } 
        else 
        {
            db::run(
                "INSERT INTO subforum_threads (subForumId, postTitle, postContents,  authorId, datePosted, canReply, bumpIndex) VALUES (:subId, :subject, :body, :author, UNIX_TIMESTAMP(), :replies, UNIX_TIMESTAMP())",
                [":subject" => $subject, ":body" => $body, ":subId" => $subForumId, ":replies" => $replies, ":author" => $userId]
            );

            header("Location: /forum/viewPost?id=" . $pdo->lastInsertId());
        }
    }
}
pageBuilder::buildHeader();
?>
<nav class="breadcrumb">
    <a href="/" class="breadcrumb-item"><?=SITE_CONFIG["site"]["name"]?></a> <a href="/forum/" class="breadcrumb-item">Forum Home</a>
    <a href="/forum/sub?id=<?= $subForumId ?>" class="breadcrumb-item"><?= $subForumInfo->subName ?></a>
    <?php if ($postInfo) { ?>
    <a href="/forum/viewPost?id=<?= $postInfo->id ?>" class="breadcrumb-item"><?= htmlspecialchars($postInfo->postTitle) ?></a>
    <?php } ?>
    <span class="breadcrumb-item active">New <?= $postInfo ? "Reply" : "Thread" ?></span>
</nav>
<h2 class="font-weight-normal">New <?= $postInfo ? 'Reply' : 'Thread' ?></h2>
<div class="row">
    <div class="col-7">
        <form method="post">
            <div class="form-group">
                <?php if (!$postInfo) { ?>
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control<?= $errors["subject"] ? ' is-invalid' : '' ?>" id="subject" name="subject" placeholder="64 characters max" value="<?= htmlspecialchars($subject) ?>" required>
                    <div class="invalid-feedback"><?= $errors["subject"] ?></div>
                <?php } else { ?>
                    <p>Replying to: <?= htmlspecialchars($postInfo->postContents) ?></p>
                <?php } ?>
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea class="form-control<?= $errors["body"] ? ' is-invalid' : '' ?>" name="body" placeholder="3-10,000 characters max" id="body" rows="3"></textarea>
                <div class="invalid-feedback"><?= $errors["body"] ?></div>
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
            <div class="card-header"><?=SITE_CONFIG["site"]["name"]?> Forum Rules</div>
            <div class="card-body">Make sure you post respectfully and follow the Site Rules when posting.</div>
        </div>
    </div>
</div>
<?php pageBuilder::buildFooter(); ?>