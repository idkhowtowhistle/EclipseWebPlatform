<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/private/core.php';
users::requireLogin();
if (isset($_GET['id'])) {
    $userid = $_GET["id"] ?? false;
    if (!$userid) pageBuilder::errorCode(404);
    $selfFriends = false;
} else {
    $userid = SESSION["userId"];
    $selfFriends = true;
}

$userInfo = users::getUserInfoFromUid($userid);
if (!$userInfo) pageBuilder::errorCode(404);
pageBuilder::$pageConfig["title"] = "Friends";
pageBuilder::buildHeader();
?>
<h2 class="font-weight-normal"><?= $selfFriends ? "My " : "$userInfo->username's " ?>Friends</h2>
<div class="card">
    <div class="card-body py-1">
        <ul class="nav nav-pills nav-fill py-1" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="pills-friends-tab" data-toggle="pill" href="#pills-friends" role="tab" aria-controls="pills-friends" aria-selected="true">Friends</a>
            </li>
            <?php if ($selfFriends) { ?>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="pills-requests-tab" data-toggle="pill" href="#pills-requests" role="tab" aria-controls="pills-requests" aria-selected="false">Requests</a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
<div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="pills-friends" role="tabpanel" aria-labelledby="pills-friends-tab">
        <div class="card mt-3">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-user-group fa-2x mb-3 d-block"></i>
                <?= $selfFriends ? "You haven't added any friends yet." : "This user hasn't added any friends yet." ?>
            </div>
        </div>
    </div>
    <?php if ($selfFriends) { ?>
        <div class="tab-pane fade" id="pills-requests" role="tabpanel" aria-labelledby="pills-requests-tab">
            <div class="card mt-3">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-envelope fa-2x mb-3 d-block"></i>
                    You don't have any pending friend requests.
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<?php pageBuilder::buildFooter(); ?>