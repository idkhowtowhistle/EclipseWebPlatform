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

pageBuilder::buildHeader();
?>
<div class="app py-4 container">
<div class="row">
    <div class="col-md-8 my-2 offset-md-2">
        <div class="alert alert-info py-2 text-center">
            <b>NOTE:</b> Passwords are hashed with <code>bcrypt</code>
        </div>
            <div class="card">
                <div class="card-header">
                    Login
                </div> 
                <div class="card-body"> 
                    <form role="form" method="POST" action="/login"> 
                    <div class="form-group row">
                        <label for="username" class="col-md-3 col-form-label">Username</label>
                         <div class="col-md-9"><input type="text" id="username" name="username" value="" required="required" autofocus="autofocus" class="form-control">
                         <?=getError("username")?>
                         </div>
                    </div>
                     <div class="form-group row">
                         <label for="password" class="col-md-3 col-form-label">Password</label> 
                         <div class="col-md-9"><input type="password" id="password" name="password" required="required" class="form-control">
                        <?=getError("password")?>
                         </div>
                    </div>
                     <div class="form-group row mb-0">
                         <div class="col-md-9 offset-md-3">
                </div>
            </div> 
                  <div class="form-group row">
                      <div class="col-md-9 offset-md-3">
                          <button type="submit" class="btn btn-primary"> Login</button>
                         </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div>
<?php pageBuilder::buildFooter(); ?>