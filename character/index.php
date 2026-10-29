<?php
require $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
require $_SERVER["DOCUMENT_ROOT"] . "/api/private/avatar.php";
users::requireLogin();

$userId = (int)SESSION["userId"];
$appearance = avatarRenderer::get($userId);
$changed = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (($_POST["polygon_csrf"] ?? "") !== SESSION["csrfToken"]) {
        pageBuilder::showStaticNotification("error", "Invalid CSRF token");
    } elseif (isset($_POST["regenerate"])) {
        avatarRenderer::clear($userId);
        avatarRenderer::unmark($userId);
        avatarRenderer::spawn($userId);
        $changed = true;
        pageBuilder::showStaticNotification("success", "Avatar queued for re-render.");
    } else {
        $appearance = [
            "head"     => $_POST["head"] ?? "",
            "torso"    => $_POST["torso"] ?? "",
            "leftArm"  => $_POST["leftArm"] ?? "",
            "rightArm" => $_POST["rightArm"] ?? "",
            "leftLeg"  => $_POST["leftLeg"] ?? "",
            "rightLeg" => $_POST["rightLeg"] ?? "",
            "face"     => isset($_POST["face"]) ? 1 : 0,
        ];
        avatarRenderer::set($userId, $appearance);
        avatarRenderer::clear($userId);
        avatarRenderer::unmark($userId);
        avatarRenderer::spawn($userId);
        $appearance = avatarRenderer::get($userId);
        $changed = true;
        pageBuilder::showStaticNotification("success", "Colors saved. Rendering...");
    }
}

$parts = [
    "head"     => "Head",
    "torso"    => "Torso",
    "rightArm" => "Right Arm",
    "leftArm"  => "Left Arm",
    "leftLeg"  => "Left Leg",
    "rightLeg" => "Right Leg",
];

pageBuilder::$pageConfig["title"] = "Character";
pageBuilder::buildHeader();

$preview = "/thumbnails/avatar?id=" . $userId . "&x=352&y=352&v=" . time();
?>
<div class="row">
    <div class="col-md-5">
        <form method="post" class="mb-2">
            <input type="hidden" name="polygon_csrf" value="<?= htmlspecialchars(SESSION["csrfToken"]) ?>">
            <button type="submit" name="regenerate" value="1" class="btn btn-block btn-secondary">
                <i class="fas fa-sync-alt mr-1"></i> Regenerate
            </button>
        </form>
        <div class="card">
            <div class="card-body text-center">
                <img src="<?= htmlspecialchars($preview) ?>" id="avatarPreview" class="avatar card-img mx-auto" style="max-width:352px;" alt="Avatar">
            </div>
        </div>
        <p class="text-muted small mt-2">
            This avatar is rendered by RCCService. After saving, it can take a few seconds to appear.
        </p>
    </div>

    <div class="col-md-7">
        <h3 class="font-weight-normal">Colors</h3>
        <div class="card">
            <div class="card-body">
                <form method="post" id="colorsForm">
                    <input type="hidden" name="polygon_csrf" value="<?= htmlspecialchars(SESSION["csrfToken"]) ?>">

                    <div class="Mannequin text-center">
                        <p>Click a body part to change its color:</p>
                        <div class="ColorChooserFrame mx-auto" style="height:240px;width:194px;position:relative;text-align:center;">
                            <div style="position:absolute;left:72px;top:0;cursor:pointer;">
                                <div class="ColorChooserRegion" data-input="head" style="background-color:<?= $appearance["head"] ?>;height:44px;width:44px;"></div>
                            </div>
                            <div style="position:absolute;left:0;top:52px;cursor:pointer;">
                                <div class="ColorChooserRegion" data-input="rightArm" style="background-color:<?= $appearance["rightArm"] ?>;height:88px;width:40px;"></div>
                            </div>
                            <div style="position:absolute;left:48px;top:52px;cursor:pointer;">
                                <div class="ColorChooserRegion" data-input="torso" style="background-color:<?= $appearance["torso"] ?>;height:88px;width:88px;"></div>
                            </div>
                            <div style="position:absolute;left:144px;top:52px;cursor:pointer;">
                                <div class="ColorChooserRegion" data-input="leftArm" style="background-color:<?= $appearance["leftArm"] ?>;height:88px;width:40px;"></div>
                            </div>
                            <div style="position:absolute;left:48px;top:146px;cursor:pointer;">
                                <div class="ColorChooserRegion" data-input="leftLeg" style="background-color:<?= $appearance["leftLeg"] ?>;height:88px;width:40px;"></div>
                            </div>
                            <div style="position:absolute;left:96px;top:146px;cursor:pointer;">
                                <div class="ColorChooserRegion" data-input="rightLeg" style="background-color:<?= $appearance["rightLeg"] ?>;height:88px;width:40px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <?php foreach ($parts as $key => $label) { ?>
                            <div class="col-sm-6 mb-2">
                                <label for="color-<?= $key ?>" class="mb-1"><?= $label ?></label>
                                <input type="color" class="form-control" id="color-<?= $key ?>" name="<?= $key ?>" value="<?= $appearance[$key] ?>">
                            </div>
                        <?php } ?>
                    </div>

                    <div class="custom-control custom-checkbox my-3">
                        <input type="checkbox" class="custom-control-input" id="face" name="face" value="1" <?= $appearance["face"] ? "checked" : "" ?>>
                        <label class="custom-control-label" for="face">Classic face</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Save colors
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(function () {
    $('.ColorChooserRegion').on('click', function () {
        var input = $('input[name="' + $(this).data('input') + '"]');
        if (input.length) {
            input.trigger('click');
        }
    });

    $('input[type="color"]').on('input change', function () {
        var name = $(this).attr('name');
        $('.ColorChooserRegion[data-input="' + name + '"]').css('background-color', $(this).val());
    });

    <?php if ($changed) { ?>
    setTimeout(function () {
        var img = document.getElementById('avatarPreview');
        if (img) {
            img.src = '/thumbnails/avatar?id=<?= $userId ?>&x=352&y=352&v=' + Date.now();
        }
    }, 5000);
    <?php } ?>
});
</script>
<?php pageBuilder::buildFooter(); ?>
