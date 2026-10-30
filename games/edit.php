<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

$gameId = $_GET["id"] ?? false;
if (!$gameId) pageBuilder::errorCode(404);
$gameInfo = games::getGameInfoFromId($gameId);
if (!$gameInfo) pageBuilder::errorCode(404);

if ($gameInfo->isDeleted) pageBuilder::errorCode(404);

if (SESSION["adminLevel"] < 2) {
    if ($gameInfo->ownerId != SESSION["userId"]) pageBuilder::errorCode(404);
}

if (SESSION["gameban"]) pageBuilder::errorCode(404);

pageBuilder::buildHeader();
?>
<div class="row">
    <div class="col-2">
        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <a class="nav-link active" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">General</a>
            <a class="nav-link" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Privacy</a>
        </div>
    </div>
    <div class="col-7">
        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                <p>
                    <button class="btn btn-success btn-sm" type="button" data-toggle="collapse" data-target="#networkCol" aria-expanded="false" aria-controls="collapseExample">
                        View network settings
                    </button>
                </p>
                <div class="collapse" id="networkCol">
                    <div class="row">
                        <div class="col">
                            <label for="serverIp">IPV4</label>
                            <input type="text" id="serverIp" class="form-control" value="<?= $gameInfo->serverIp ?>" placeholder="IP Address">
                        </div>
                        <div class="col">
                            <label for="serverPort">Port</label>
                            <input type="text" id="serverPort" value="<?= $gameInfo->serverPort ?>" class="form-control" placeholder="Port">
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col">
                        <label for="limit">Server Name</label>
                        <input type="text" id="serverName" value="<?= $gameInfo->serverName ?>" class="form-control" placeholder="Server Name">
                    </div>
                    <div class="col">
                        <label for="limit">Player Limit</label>
                        <input type="number" min="1" value="<?= $gameInfo->serverSize ?>" placeholder="Server Limit" class="form-control" name="limit" id="limit" value="1">
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <label for="serverDesc">Server Description</label>
                    <textarea class="form-control" id="serverDesc" rows="3"><?= $gameInfo->serverDesc ?></textarea>
                </div>
                <button type="button" data-control="serverEdit" class="btn btn-sm btn-success"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Save Changes</button>
                <hr>
                <div class="row">
                    <div class="col-3">
                        <input class="form-control form-control-sm" id="del-serverName" type="text" placeholder="Server Name">
                    </div>
                        <button type="button" id="deleteServ" data-control="deleteServer" class="btn btn-sm btn-danger"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Delete Server</button>
                </div>
                <small for="deleteServ">NOTE: This action is irreversible!</small>
            </div>
            <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                <h4>Private Key</h4>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" data-control="privateKey" value="<?= siteBaseUrl() ?>/games/view?id=<?= $gameInfo->id ?>&privateKey=<?= $gameInfo->privateKey ?>" placeholder="Private key" id="privKey" aria-label="Private Key" aria-describedby="basic-addon2" disabled>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" data-control="regenKey" type="button"><i class="fas fa-sync"></i></button>
                    </div>
                </div>
                <hr>
                <h4>Private Game</h4>
                <input type="checkbox" <?= $gameInfo->isPrivate ? "checked" : "" ?> id="privacy" data-toggle="toggle" data-size="sm">
                <button type="button" data-control="setPrivacy" class="btn btn-sm btn-success"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Confirm</button>
            </div>
        </div>
    </div>
</div>
<script>
    $("button[data-control$='serverEdit']").on("click", this, function() {
        var button = this;
        $(button).attr("disabled", "disabled").find("span").show();
        $.post("/api/games/editServer", {
            "serverId":<?=$gameInfo->id?>,
            "serverIp":$("#serverIp").val(),
            "serverPort":$("#serverPort").val(),
            "serverName":$("#serverName").val(),
            "serverSize":$("#limit").val(),
            "serverDesc":$("#serverDesc").val()
        }, function(data) {
            if (data.success) {
                toastr["success"]("Saved server settings");
            } else {
                toastr["error"](data.message);
            }
            $(button).removeAttr("disabled").find("span").hide();
        });
    })
    $("button[data-control$='deleteServer']").on("click", this, function() {
        var button = this;
        $(button).attr("disabled", "disabled").find("span").show();
        $.post("/api/games/deleteServer", {
            "gameId": <?= $gameInfo->id ?>,
            "serverName": $("#del-serverName").val()
        }, function(data) {
            if (data.success) {
                window.location.replace("https://banland.xyz/games");
            } else {
                toastr["error"](data.message);
            }
            $(button).removeAttr("disabled").find("span").hide();
        });
    })
    $("button[data-control$='setPrivacy']").on("click", this, function() {
        var button = this;
        $(button).attr("disabled", "disabled").find("span").show();
        $.post("/api/games/updatePrivacy", {
            "gameId": <?= $gameInfo->id ?>,
            "privacy": $("#privacy").is(":checked")
        }, function(data) {
            if (data.success) {
                toastr["success"]("Updated privacy setting");
            } else {
                toastr["error"](data.message);
            }
            $(button).removeAttr("disabled").find("span").hide();
        });
    })

    $("button[data-control$='regenKey']").on("click", this, function() {
        var button = this;
        $(button).attr("disabled", "disabled");
        $.post('/api/games/regenKey', {
            "gameId": <?= $gameInfo->id ?>
        }, function(data) {
            if (data.success) {
                $("input[data-control$='privateKey']").val(data.message);
                toastr["success"]("Updated private key");
            } else {
                toastr["error"](data.message);
            }
            $(button).removeAttr("disabled");
        });
    })
</script>

<?php pageBuilder::buildFooter(); ?>