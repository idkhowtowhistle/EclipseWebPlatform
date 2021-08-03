<?php
require $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if (!SESSION) redirect("/");
$userInfo = users::getUserInfoFromUid(SESSION["userId"]);
$gauth = twofa::initialize();

$userinfo = (object)SESSION["userInfo"];

$twofa = SESSION["2fa"];
$twofaSecret = $userinfo->twofaSecret;

if (isset($_POST["2fa"])) {
  $csrf = $_POST['polygon_csrf'] ?? false;
  $code = $_POST['code'] ?? false;
  $password = $_POST['password'] ?? false;


  if ($csrf != SESSION["csrfToken"]) {
    pageBuilder::showStaticNotification("error", "Invalid CSRF token");
    goto pb;
  }

  if (!$gauth->checkCode($twofaSecret, $code, 1)) {
    pageBuilder::showStaticNotification("error", "Incorrect code");
    goto pb;
  }

  if (!password_verify($password . SITE_CONFIG["site"]["pwsalt"], $userinfo->password)) {
    pageBuilder::showStaticNotification("error", "Incorrect password");
    goto pb;
  }

  twofa::toggle();
  $twofa = !SESSION["2fa"];

  if ($twofa) {
    $recoveryCodes = twofa::generateRecoveryCodes();
    ob_start();
?>
    Congratulations! Your account is now more secure. But, before you go there's one last thing which is <b>really important</b>.
    <br><br>
    If all else fails, if you just cannot regain access to your 2FA app codes, you can use a 2FA recovery code.
    <br><br>
    <div class="row" style="max-width: 16rem; margin: auto;">
      <?php foreach ($recoveryCodes as $code) { ?>
        <div class="col-sm-6">
          <code><?= $code ?></code>
        </div>
      <?php } ?>
    </div>
    <br>
    These are a set of static, one-time use codes that never expire unless they are used or you re-enable 2FA. You can use these to get back into your account without the need of a 2FA app.
    <br><br>
    This is the only time you'll ever see these here, so write them down somewhere <b>now</b>.
<?php
    pageBuilder::showStaticModal(
      "Two-Factor Authentication is now active",
      ob_get_clean(),
      "[{'class':'btn btn-outline-primary', 'isDismissButton':true, 'text':'I understand'}]",
      "{'show':true, 'backdrop':'static'}"
    );
  }
} elseif (!$userinfo->twofa) {
  $twofaSecret = $gauth->generateSecret();
  $query = $pdo->prepare("UPDATE users SET twofaSecret = :secret WHERE id = :uid");
  $query->bindParam(":uid", $userinfo->id, PDO::PARAM_INT);
  $query->bindParam(":secret", $twofaSecret, PDO::PARAM_STR);
  $query->execute();
}
pb:
pageBuilder::buildHeader();
?>
<div class="app py-4 container">
  <?php if (SESSION["ssc"]) { ?>
    <div class="alert alert-danger" role="alert">
      Your account has been restricted. You cannot message, create invites, use the forums or chat in-game. Appeals can be sent to Discord.
    </div>
  <?php } ?>
  <h3 class="my-4 font-weight-normal">Settings</h3>
  <div class="row py-4">
    <div class="col-2">
      <div class="list-group" id="list-tab" role="tablist">
        <a aria-selected="true" aria-controls="admin" role="tab" href="#list-admin" data-toggle="list" id="list-admin-list" class="list-group-item list-group-item-action active">Profile</a>
        <a class="list-group-item list-group-item-action" id="list-mod-list" data-toggle="list" href="#list-mod" role="tab" aria-controls="mod" aria-selected="false">Account</a>
        <a aria-selected="false" aria-controls="audits" role="tab" href="#list-audits" data-toggle="list" id="list-audits-list" class="list-group-item list-group-item-action">Security</a>
      </div>
    </div>
    <div class="col-10">
      <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade active show" id="list-admin" role="tabpanel" aria-labelledby="list-admin-list">
          <div class="container">
            <div class="d-flex align-items-start">
              <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="v-pills-general" role="tabpanel" aria-labelledby="v-pills-general-tab">
                  <div class="row">
                    <div class="col-lg">
                      <h4 class="font-weight-normal mb-3">Blurb</h4>
                      <form>
                        <textarea class="form-control" id="biotext" name="biotext" rows="6" cols="60"><?= $userInfo->blurb ?></textarea>
                        <button class="btn btn-success mt-3 btn-block" id="edit-btn" name="editbio-btn" data-control="updateAccount" type="button"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Save Changes</button>
                      </form>
                    </div>
                    <div class="col-lg">
                      <h4 class="font-weight-normal mb-3">Messages</h4>
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="message" <?= $userInfo->messages ? "checked" : "" ?> value="true">
                        <label class="form-check-label" for="message">Allow messages</label>
                      </div>
                      <br>
                      <h4 class="font-weight-normal mb-3">Site Theme</h4>
                      <div class="form-group">
                        <label for="themeSelect">Choose a preferred site theme</label>
                        <select class="form-control" id="themeSelect">
                          <option value="0" <?=$userInfo->theme == 0?"selected":""?>>Mercury Light (default)</option>
                          <option value="1" <?=$userInfo->theme == 1?"selected":""?>>Polygon</option>
                          <option value="2" <?=$userInfo->theme == 2?"selected":""?>>Finobelium</option>
                        </select>
                      </div>
                      <script>
                        $("[data-control='updateAccount']").on("click", this, function() {
                          var btn = this;
                          $(btn).attr("disabled", "disabled").find("span").show();
                          $.post('/api/account/updateBio', {
                            "bio": $("#biotext").val(),
                            "message": $("#message").is(":checked"),
                            "theme": $("#themeSelect").val()
                          }, function(data) {
                            if (data.success) {
                              toastr["success"](data.message);
                            } else {
                              toastr["error"](data.message);
                            }
                            $(btn).removeAttr("disabled").find("span").hide();
                          })
                        })
                      </script>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane fade" id="list-mod" role="tabpanel" aria-labelledby="list-mod-list">
          <div class="container">
            <div class="form-group mb-3 col-lg-8">
              <h4 class="font-weight-normal">Change your password</h4>
            </div>
            <div class="form-group mb-3 col-lg-8">
              <label for="password">Current Password</label>
              <input type="password" name="password" id="currentpass" class="form-control">
            </div>
            <div class="form-group mb-3 col-lg-8">
              <label for="password">New Password</label>
              <input type="password" name="password" id="newpass" class="form-control">
            </div>
            <div class="form-group mb-3 col-lg-8">
              <label for="password">Confirm Password</label>
              <input type="password" name="password" id="confnewpass" class="form-control">
            </div>
            <div class="form-group col-lg-8">
              <button class="btn btn-success btn-block" id="changePass" name="changePass" data-control="changePass" type="button"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Change</button>
            </div>
          </div>
          <script>
            $("[data-control='changePass']").on("click", this, function() {
              var btn = this;
              $(btn).attr("disabled", "disabled").find("span").show();
              $.post('/api/account/changePass', {
                "currentpwd": $("#currentpass").val(),
                "newpwd": $("#newpass").val(),
                "confnewpwd": $("#confnewpass").val()
              }, function(data) {
                if (data.success) {
                  toastr["success"]("Your password has been updated");
                } else {
                  toastr["error"](data.message);
                }
                $(btn).removeAttr("disabled").find("span").hide();
              })
            })
          </script>
        </div>
        <div class="tab-pane fade" id="list-audits" role="tabpanel" aria-labelledby="list-audits-list">
          <div class="container">
            <?php if ($twofa) { ?>
              <p>Two-Factor Authentication is currently active. If you wish to disable it, just fill in the fields below.</p>
              <p>If you disable 2FA your old recovery codes will be invalidated, so just be mindful of that.</p>
            <?php } else { ?>
              <div class="row">
                <div class="col-md-6">
                  <h4 class="font-weight-normal">2FA Setup</h4>
                  <p class="mb-2">Open up your 2FA app and scan the QR code below:</p>
                  <img src="<?= $gauth->getURL(SESSION["userName"], "Mercury", $twofaSecret) ?>">
                  <br>
                  <p class="mb-4 mt-2 mb-md-0">If your 2FA app does not support QR codes, enter in the following authentication code: <code><?= $twofaSecret ?></code></p>
                </div>
                <div class="col-md-6">
                  <h4 class="font-weight-normal">Verification</h4>
                  <p>Your 2FA app will prompt you with a 6 digit code (or longer depending on what you used) - To finish the setup, enter your code below and click Enable, your account should have 2FA enabled now.</p>
                <?php } ?>
                <form method="post">
                  <div class="form-group row">
                    <label for="code" class="col-md-3 col-form-label">2FA token</label>
                    <div class="col-md-9">
                      <input type="number" name="code" id="code" value="" required="required" autofocus="autofocus" class="form-control">
                    </div>
                    <label for="password" class="col-md-3 mt-2 col-form-label">Password</label>
                    <div class="col-md-9 mt-2">
                      <input type="password" name="password" id="password" class="form-control">
                    </div>
                  </div>
                  <div class="form-group">
                    <input type="hidden" name="polygon_csrf" value="<?= SESSION["csrfToken"] ?>">
                    <input type="hidden" name="twofa_state" value="<?= $twofa ?>">
                    <button type="submit" name="2fa" class="btn btn-<?= $twofa ? 'danger' : 'primary' ?> btn-block"><?= $twofa ? 'Dis' : 'En' ?>able</button>
                  </div>
                </form>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php pageBuilder::buildFooter(); ?>