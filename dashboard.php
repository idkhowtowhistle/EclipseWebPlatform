<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

$newsInfo = news::getNewsInfo(1);
//mkdown
$markdown = new Parsedown();
$markdown->setMarkupEscaped(true);
$markdown->setBreaksEnabled(true);
$markdown->setSafeMode(true);
$markdown->setUrlsLinked(true);

pageBuilder::$pageConfig["title"] = "Home";
pageBuilder::buildHeader();
?>
<div class="app py-4 container">
<?php if(SESSION["ssc"]) { ?>
<div class="alert alert-danger" role="alert">
  Your account has been restricted. You cannot message, create invites, use the forums or chat in-game. Appeals can be sent to Discord.
</div>
<?php } ?>
<div class="mgreet">
	<img src="/thumbnails/headshot?id=<?=SESSION["userId"]?>&x=250&y=250" alt="<?=SESSION["userName"]?>">
	<h1>Hello, <a class="mgreet-name" href="/user/<?=SESSION["userId"]?>"><?=SESSION["userName"]?></a>!</h1>
</div>
<div class="row">
	<div class="col-lg-3">
		<div class="card text-center p-3">
			<img src="/thumbnails/avatar?id=<?=SESSION["userId"]?>&x=420&y=420" alt="<?=SESSION["userName"]?>" class="img-fluid">
		</div>
		<h1 class="text-center mt-3" style="font-weight: 300"><?=SITE_CONFIG["site"]["name"]?></h1>
	</div>
	<div class="col-lg-9">
		<div class="card mb-3">
			<div class="card-header">Your Wall</div>
			<div class="card-body">
				<p class="mb-0 text-muted">Stats will be posted here!</p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4 mb-3">
				<a class="mstat" href="/friends">
					<div>
						<h6>Friends</h6>
						<span class="mstat-value">0</span>
					</div>
					<i class="fas fa-user-friends"></i>
				</a>
			</div>
			<div class="col-md-4 mb-3">
				<a class="mstat" href="/economy">
					<div>
						<h6>Rocks</h6>
						<span class="mstat-value"><?=SESSION["currency"]?></span>
					</div>
					<i class="fas fa-gem"></i>
				</a>
			</div>
			<div class="col-md-4 mb-3">
				<div class="mstat">
					<div>
						<h6>Random</h6>
						<span style="font-size: 0.95rem"><?=users::generateRandomFact()?></span>
					</div>
					<i class="fas fa-shuffle"></i>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header">News &ndash; "<?=$newsInfo->title?>" &ndash; <?=date("n/j/Y", $newsInfo->date)?></div>
			<div class="card-body">
				<?=$markdown->text($newsInfo->news)?>
				<p class="mb-0 text-muted">&ndash; <?=users::getUsernameFromUid($newsInfo->createdBy)?></p>
			</div>
		</div>
	</div>
</div>
</div>
<?php pageBuilder::buildFooter(); ?>
