<?php
require $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if (!SESSION) redirect("/");
$userInfo = users::getUserInfoFromUid(SESSION["userId"]);
$gauth = twofa::initialize();

$userinfo = (object)SESSION["userInfo"];
$discordInfo = (object)
[
  "info" => NULL,
  "key" => $userinfo->discordKey,
  "timeVerified" => $userInfo->discordVerifyTime
];

if ($discordInfo->key == NULL)
{
  $discordInfo->key = generateUUID();
  db::run(
    "UPDATE users SET discordKey = :key WHERE id = :id", 
    [":key" => $discordInfo->key, ":id" => SESSION["userId"]]
  );
}
else if ($userInfo->discordId != NULL)
{
  $discordInfo->info = getDiscordInfo($userInfo->discordId);
}

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
pageBuilder::$pageConfig["title"] = "Settings";
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
    <div class="col-sm-4 col-md-3 col-lg-2">
      <div class="list-group" id="list-tab" role="tablist">
        <a class="list-group-item list-group-item-action active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">Profile</a>
        <a class="list-group-item list-group-item-action" id="account-tab" data-toggle="tab" href="#account" role="tab" aria-controls="account" aria-selected="false">Account</a>
        <a class="list-group-item list-group-item-action" id="security-tab" data-toggle="tab" href="#security" role="tab" aria-controls="security" aria-selected="false">Security</a>
        <a class="list-group-item list-group-item-action" id="discord-tab" data-toggle="tab" href="#discord" role="tab" aria-controls="discord" aria-selected="false">Discord</a>
      </div>
    </div>
    <div class="col-sm-8 col-md-9 col-lg-10">
      <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade active show" id="profile" role="tabpanel" aria-labelledby="profile-tab">
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
            </div>
            <script>
              $("[data-control='updateAccount']").on("click", this, function() {
                var btn = this;
                $(btn).attr("disabled", "disabled").find("span").show();
                $.post('/api/account/updateBio', {
                  "bio": $("#biotext").val(),
                  "message": $("#message").is(":checked"),
                  "theme": "0"
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
        <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="account-tab">
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
        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
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
          <?php if (!$twofa) { ?>
            </div>
          </div>
          <?php } ?>
        </div>
        <div class="tab-pane fade" id="discord" role="tabpanel" aria-labelledby="discord-tab">
          <div class="row">
            <div class="col-sm-6">
              <?php if ($discordInfo->info == NULL) { ?>
              <h4 class="font-weight-normal">Mercury Discord Verification</h4>
              <p class="mb-1">Looks like you're not yet verified. If you haven't joined the server yet, you can do so with that handy widget over by the side.</p>
              <p class="mb-1">Once you join, the Mercury bot should DM you asking for your verification key, which is here:</p>
              <code class="mb-1">MercuryVerify:<?=$discordInfo->key?></code>
              <p class="mb-1">Just send this to the bot, and you'll be verified!</p> 
              <p class="mb-1">If the bot hasn't DMed you, it may be down. When it comes back online, just send a DM to the bot with your key.</p> 
              <?php } else { ?>
              <h4 class="font-weight-normal">Your Discord Account</h4>
              <div class="card bg-light mb-3">
                <div class="card-body">
                  <div class="row">
                    <div class="col-sm-3">
                      <img class="img-fluid rounded-circle" src="<?=$discordInfo->info->avatar?>">
                    </div>
                    <div class="col-sm-9">
                      <h4 class="font-weight-normal"><?=$discordInfo->info->username?><span class="text-muted">#<?=$discordInfo->info->tag?></span></h4>
                      <h5 class="font-weight-normal">Verified <?=general::time_elapsed("@".$discordInfo->timeVerified)?></h5>
                    </div>
                  </div>
                </div>
              </div>
              <p class="mb-1">If you wish to have your Discord account unverified so you can use another account, message an admin.</p> 
              <?php } ?>
            </div>
            <div class="col-sm-6">
              <h4 class="font-weight-normal">Join the Discord Server!</h4>
              <iframe src="https://discord.com/widget?id=846501584703062066&theme=dark" width="350" height="500" allowtransparency="true" frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php pageBuilder::buildFooter(); ?>