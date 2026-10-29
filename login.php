<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if(SESSION) redirect("/");

$errors = ["username" => false, "password" => false];
$username = $password = false;

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
  $password = $_POST['password'] ?? false;
  $returnurl = $_GET['ReturnUrl'] ?? false;
  $userInfo = users::getUserInfoFromUserName($username);

  if(!$username) $errors["username"] = "Please enter your username";
  if(!$userInfo) $errors["username"] = "This user doesn't exist";

  if(!$password) $errors["password"] = "Please enter your password";
  elseif(!$userInfo || !password_verify($password.SITE_CONFIG["site"]["pwsalt"], $userInfo->password)) $errors["password"] = "Incorrect password";

  if(count(array_unique($errors)) === 1 && !current($errors)) //if all errors are false
  {
    session::create($userInfo->id);
    if($userInfo->twofa)
		{
      if($returnurl) die(header("Location: /auth/twofa?ReturnUrl=".$returnurl));
			die(header("Location: /auth/twofa")); 
		}
		else
		{
      if($returnurl) die(header("Location: ".$returnurl));
			die(header("Location: /")); 
		}
  }
}

pageBuilder::$pageConfig["title"] = "Login";
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
			<h2>Welcome back</h2>
			<p>Log in to keep playing, building and chatting with the community.</p>
		</div>
		<div>
			<h2>Invite key protected</h2>
			<p><?= SITE_CONFIG["site"]["name"] ?> is invite-only. Ask a friend for a key or reach out on Discord.</p>
		</div>
	</div>
	<div class="mshell-right">
		<div class="mshell-form">
			<h2>Log into your account</h2>
			<p>Don't yet have an account? <a href="/register">Register</a></p>
			<form role="form" method="POST" action="/login">
				<div class="form-group">
					<label for="username">Username</label>
					<input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?: "") ?>" required="required" autofocus="autofocus" autocomplete="username" class="form-control<?= getLabel("username") ?>">
					<?= getError("username") ?>
				</div>
				<div class="form-group">
					<label for="password">Password</label>
					<input type="password" id="password" name="password" required="required" autocomplete="current-password" class="form-control<?= getLabel("password") ?>">
					<?= getError("password") ?>
				</div>
				<button type="submit" class="btn btn-primary">Log in</button>
			</form>
		</div>
	</div>
</div>
<?php pageBuilder::buildFooter(); ?>