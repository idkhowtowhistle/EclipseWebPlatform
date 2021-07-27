<?php
$bypassModeration = true;
require $_SERVER['DOCUMENT_ROOT'].'/api/private/core.php';


if(!SESSION) die(header("Location: /")); 
if(!SESSION["2fa"] || SESSION["2faVerified"]) die(header("Location: /dashboard"));

$gauth = twofa::initialize();
$sesskey = SESSION["sessionKey"];
$uid = SESSION["userId"];
$error = false;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$code = $_POST['code'] ?? false;
	$recoveryCodes = json_decode(SESSION["userInfo"]["twofaRecoveryCodes"], true);

	if(!$code) $error = "Please enter a 2FA code or a recovery code";
	elseif(is_numeric($code) && !$gauth->checkCode(SESSION["userInfo"]["twofaSecret"], $code, 1)) 
		$error = "Incorrect 2FA code";
	elseif(!is_numeric($code) && (!isset($recoveryCodes[$code]) || !$recoveryCodes[$code]))
		$error = "Invalid recovery code";
	else
	{
		//invalidate recovery code
		if(!is_numeric($code))
		{
			$recoveryCodes[$code] = false;
			$recoveryCodes = json_encode($recoveryCodes);
			$query = $pdo->prepare("UPDATE users SET twofaRecoveryCodes = :recoveryCodes WHERE id = :uid");
			$query->bindParam(":recoveryCodes", $recoveryCodes, PDO::PARAM_STR);
			$query->bindParam(":uid", $uid, PDO::PARAM_INT);
			$query->execute();
		}

		$query = $pdo->prepare("UPDATE sessions SET twofaVerified = 1 WHERE sessionKey = :key");
		$query->bindParam(":key", $sesskey, PDO::PARAM_STR);
		$query->execute();

		if(isset($_GET['ReturnUrl'])) die(header("Location: ".$_GET['ReturnUrl']));
		die(header("Location: /")); 
	}
}


pageBuilder::buildHeader();
?>

<div class="app py-4 container">
    <div class="d-flex justify-content-center">
        <div class="card" style="width: 40rem;">
            <div class="card-header">Two-Factor Authentication</div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group row">
                            <label for="currentpwd" class="col-sm-3 col-form-label">2FA Code</label>
                            <div class="col-sm-9">
                                <input type="text" name="code" id="code" class="form-control" max="10">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <button class="btn btn-success btn-block" type="submit">Validate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php pageBuilder::buildFooter() ?>