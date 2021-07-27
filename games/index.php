<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
pageBuilder::buildHeader();


$serverName = isset($_POST['search']) ? "%" . $_POST['search'] . "%" : "%";
$uid = SESSION["userId"];
$query = $pdo->prepare("SELECT * FROM games WHERE serverName LIKE :serverName AND (ownerId = :uid OR NOT isPrivate) AND NOT isDeleted");
$query->bindParam(":serverName", $serverName, PDO::PARAM_STR);
$query->bindParam(":uid", $uid, PDO::PARAM_INT);
$query->execute();

?>
<div class="alert alert-primary" role="alert">Self hosting is NOT final. Read <a href="/games/help">here.</a></div>
<?php if (SESSION["gameban"]) { ?>
	<div class="alert alert-danger" role="alert">Your account has been game banned. You cannot host/join any server.</div>
<?php } ?>
<div class="row">
	<div class="col-lg-4 mt-3" style="display: block;">
		<div class="card">
			<div class="card-body">
				<a href="https://setup.banland.xyz/MercuryPlayerLauncher.exe" type="button" data-toggle="tooltip" data-placement="top" title="Completely safe - <?=SITE_CONFIG["site"]["name"]?> 2013." class="btn btn-block btn-success">Download Client</a>
				<a href="/games/new" type="button" data-toggle="tooltip" data-placement="top" title="It's easy to get started!" class="btn btn-block btn-info">Host Server <span class="badge badge-pill badge-warning">alpha</span></a>
				<small><a href="/help/hosting">Hosting help</a></small>
			</div>
		</div>
	</div>
	<div id="games" class="col-lg-8 row mt-3">
		<div class="w-100">
			<form class="mb-3 d-flex" method="post">
				<input aria-controls="bannerTable" id="search" name="search" value="<?= $_POST['search'] ?? '' ?>" placeholder="Search for a game" class="form-control w-100" type="search">
				<button type="submit" class="btn btn-secondary d-inline ml-2 px-4"><i class="fas fa-search"></i></button>
				<a type="button" href="/games" class="btn btn-danger d-inline ml-2 px-4"><i class="fas fa-redo-alt"></i></a>
			</form>
		</div>
		<?php if ($query->rowCount()) { ?>
			<?php while ($row = $query->fetch(PDO::FETCH_OBJ)) { $isHost = games::isHosting($row->id);?>
				<div class="col-md-3 px-2 pb-3">
					<a href="/games/view?id=<?= $row->id ?>" class="text-reset text-decoration-none">
					</a>
					<div class="card hover-shadow">
						<a href="/games/view?id=<?= $row->id ?>" class="text-reset text-decoration-none">
						</a>
						<div class="card-body">
							<a href="/games/view?id=<?= $row->id ?><?=$row->ownerId === SESSION["userId"] && $row->isPrivate ? "&privateKey=$row->privateKey" : ""?>" class="text-reset text-decoration-none">
								<h5 style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= $row->serverName ?></h5>
								<hr>
								<small>By:
								</small>
							</a>
							<small><a href="/user?id=<?= $row->ownerId ?>"><?= users::getUsernameFromUid($row->ownerId) ?></a></small>
							<br>
							<small>Status: <span class="badge badge-<?=($isHost['online']?"success":"danger")?>"><?=($isHost['online']?"Active":"Offline")?></span></small>
							<br>
							<small>Playing: <?= games::getPlayerCountInServer($row->id) ?>/<?= $row->serverSize ?></small>
						</div>
					</div>
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="container text-center">
				<p>Could not find any results <a class="btn btn-danger btn-sm" href="/games">Reset search</a></p>
			</div>
		<?php } ?>
	</div>
</div>
<?php pageBuilder::buildFooter(); ?>