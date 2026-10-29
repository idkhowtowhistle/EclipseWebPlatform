<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if(SESSION) redirect("/");

$ip = getIPAddress();
// $ipCheck = db::run("SELECT COUNT(*) FROM users WHERE ip = :ip", [":ip" => $ip])->fetchColumn();
// if ($ipCheck >= 2) pageBuilder::errorCode(200, ["title" => "Account limit reached", "text" => "You can only create a maximum of two accounts"]);

$errors = ["username" => false, "email" => false, "password" => false, "confirmpassword" => false, "invitekey" => false];//, "captcha" => false];
$username = $email = $password = $confirmpassword = $invitekey = false;//$captcha = false;

function swearFilter($text)
{
    $swearList = ["shit", "fuck", "nigg", "nigga", "Nig", "NIg", "NIG", "nig", "tranny", "faggot"];
    if (str_ireplace($swearList, "", $text) != $text) return true;
}

function getLabel($field)
{
	if($_SERVER['REQUEST_METHOD'] != "POST") return;
	global $errors;
	$class = " is-";
	$class .= $errors[$field] ? "invalid" : "valid";
	return $class;
}

function getError($field)
{
	global $errors;
	if(!$errors[$field]) return;
	return '<div class="invalid-feedback d-block">'.$errors[$field].'</div>';
}

if($_SERVER['REQUEST_METHOD'] == "POST")
{
	$username = $_POST['username'] ?? false;
	// $captcha = $_POST["g-recaptcha-response"] ?? false;
	$email = $_POST['email'] ?? false;
	$password = $_POST['password'] ?? false;
	$confirmpassword = $_POST['confirmpassword'] ?? false;
	$invitekey = $_POST["invitekey"] ?? false;
	$blurb = "Welcome to ".SITE_CONFIG["site"]["name"]."!";

	if (!$username) 
		$errors["username"] = "Please enter a username";
	elseif (strlen($username) < 3 || strlen($username) > 20) 
		$errors["username"] = "Username must be between 3 and 20 characters";
	elseif (!ctype_alnum($username)) 
		$errors["username"] = "Your username can only contain letters and numbers";
	elseif (swearFilter($username))
		$errors["username"] = "You can't have an inappropriate username!";
	elseif (users::getUidFromUsername($username))
		$errors["username"] = "Seems like someone else already has that username, sorry :(";

	// $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . SITE_CONFIG['captcha']['privateKey'] . "&response=".$_POST['g-recaptcha-response']);
    // $responseData = json_decode($verifyResponse);
	// if(!$responseData->success) 
	// 	$errors["captcha"] = "Could not verify CAPTCHA, Please try again";

	if (!$email)
		$errors["email"] = "Please enter your e-mail address";
	elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
		$errors["email"] = "That's not a valid e-mail address";
	elseif (strlen($email) > 255)
		$errors["email"] = "Your e-mail address must be shorter than 255 characters - how is your e-mail that long??";
	elseif (db::run("SELECT COUNT(*) FROM users WHERE email = :email", [":email" => $email])->fetchColumn())
      	$errors["email"] = "Seems like someone else has already registered with that e-mail address";

	if (!$password)
		$errors["password"] = "Please enter a password";
	elseif (strlen($password) < 8)
		$errors["password"] = "Your password must be at least 8 characters";
	elseif (!strlen(preg_replace('/[^0-9]/', "", $password)))
		$errors["password"] = "Your password is too weak. Make sure it contains at least one number";
	elseif (strlen(preg_replace('/[0-9]/', "", $password)) < 6)
		$errors["password"] = "Your password is too weak. Make sure it contains at least six non-numeric characters";

	if (!$confirmpassword)
		$errors["confirmpassword"] = "Please enter a password";
	elseif ($password != $confirmpassword)
		$errors["confirmpassword"] = "Passwords do not match";

	if (!$invitekey)
		$errors["invitekey"] = "Please enter an invite key";
	elseif (!invite::validateInviteKey($invitekey))
		$errors["invitekey"] = "This invite key is invalid";

  	if(count(array_unique($errors)) === 1 && !current($errors)) //if all errors are false
  	{
  		// TODO: use password_lock instead
    	$passwordhash = password_hash($password.SITE_CONFIG["site"]["pwsalt"], PASSWORD_BCRYPT);

    	db::run(
    		"INSERT INTO users (username, password, email, blurb, joined, lastonline, ip) VALUES (:username, :password, :email, :blurb, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :ip)",
    		[":username" => $username, ":password" => $passwordhash, ":email" => $email, ":blurb" => $blurb, ":ip" => $ip]
    	);

    	session::create($pdo->lastInsertId());
    	invite::useKey($invitekey, $username);
    	redirect("/dashboard");
  	}
}

pageBuilder::$pageConfig["title"] = "Register";
pageBuilder::$pageConfig["navbar"] = false;
pageBuilder::$pageConfig["bodyClass"] = "plain";
pageBuilder::buildHeader();
?>

<div class="mshell">
	<div class="mshell-left">
		<h1><?= SITE_CONFIG["site"]["name"] ?></h1>
		<h2 class="mshell-tagline">&ndash; Be Anything, Build Anything.</h2>
		<hr>
		<div>
			<h2>Join the community</h2>
			<p>Create an account to play, build and chat with everyone else on <?= SITE_CONFIG["site"]["name"] ?>.</p>
		</div>
		<div>
			<h2>Invite key required</h2>
			<p>Registration is invite-only. Ask a friend for a one-time key, or reach out on Discord.</p>
		</div>
		<div>
			<h2>Stay safe</h2>
			<p>Use a unique password and a valid email &ndash; you will need it to recover your account.</p>
		</div>
	</div>
	<div class="mshell-right">
		<div class="mshell-form">
			<h2>Create your account</h2>
			<p>Already have an account? <a href="/login">Log in</a></p>
			<form role="form" method="POST" action="/register">
				<div class="form-group">
					<label for="username">Username</label>
					<input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?: "") ?>" required="required" autofocus="autofocus" autocomplete="username" class="form-control<?= getLabel("username") ?>">
					<small id="nameHelp" class="form-text text-muted">Your account will be banned if you sign up with an inappropriate name.</small>
					<?= getError("username") ?>
				</div>
				<div class="form-group">
					<label for="email">Email Address</label>
					<input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?: "") ?>" required="required" autocomplete="email" class="form-control<?= getLabel("email") ?>">
					<small id="email" class="form-text text-muted">Please give us a valid email, if you use an invalid email you will lose your account.</small>
					<?= getError("email") ?>
				</div>
				<div class="form-group">
					<label for="password">Password</label>
					<input type="password" id="password" name="password" required="required" autocomplete="new-password" class="form-control<?= getLabel("password") ?>">
					<small id="nameHelp" class="form-text text-muted">Passwords should be 8 or more characters.</small>
					<?= getError("password") ?>
				</div>
				<div class="form-group">
					<label for="confirmpassword">Confirm Password</label>
					<input type="password" id="confirmpassword" name="confirmpassword" required="required" autocomplete="new-password" class="form-control<?= getLabel("confirmpassword") ?>">
					<?= getError("confirmpassword") ?>
				</div>
				<div class="form-group">
					<label for="invitekey">Invite Key</label>
					<input type="text" id="invitekey" name="invitekey" required="required" class="form-control<?= getLabel("invitekey") ?>">
					<small id="invitekey" class="form-text text-muted">Invite Keys are <i>one time</i> only.</small>
					<?= getError("invitekey") ?>
				</div>
				<p class="text-center">By registering on this site, <b>you agree to our <a href="/rules">rules</a>.</b></p>
				<button type="submit" class="btn btn-primary">Register</button>
			</form>
		</div>
	</div>
</div>
<?php pageBuilder::buildFooter(); ?>
