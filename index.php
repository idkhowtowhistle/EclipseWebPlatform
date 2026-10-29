<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if (SESSION) {
   redirect("/dashboard");
}

pageBuilder::$pageConfig["title"] = "Home";
pageBuilder::$pageConfig["navbar"] = false;
pageBuilder::$pageConfig["bodyClass"] = "plain";
pageBuilder::buildHeader();
?>

<div class="mlanding">
	<div class="mlanding-gradient"></div>
	<div class="mlanding-waves"><div></div><div></div></div>
	<div class="mlanding-info">
		<h1><?= SITE_CONFIG["site"]["name"] ?></h1>
		<p class="mlanding-tagline">Be Anything, Build Anything.</p>
		<div class="mlanding-actions">
			<a class="btn btn-sm btn-secondary" href="/login"><b>Login</b> <i class="fas fa-right-to-bracket pl-1"></i></a>
			<a class="btn btn-sm btn-primary" href="/register"><b>Register</b> <i class="fas fa-plus pl-1"></i></a>
		</div>
		<a class="btn btn-sm btn-tertiary" href="/about">About us <i class="fas fa-circle-question pl-1"></i></a>
		<p class="mlanding-note">Invite key required to join &ndash; ask a friend!</p>
	</div>
</div>
<?php pageBuilder::buildFooter(); ?>