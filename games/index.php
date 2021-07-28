<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
pageBuilder::buildHeader();


$serverName = isset($_POST['search']) ? "%" . $_POST['search'] . "%" : "%";
$uid = SESSION["userId"];

$games = db::run(
	"SELECT *, users.username, (serverPing+35 > UNIX_TIMESTAMP()) AS serverOnline,
	(SELECT COUNT(*) FROM game_sessions WHERE serverId = games.id AND valid AND ping+35 > UNIX_TIMESTAMP() GROUP BY userId) AS playerCount
	FROM games INNER JOIN users ON users.id = ownerId WHERE serverName LIKE :serverName AND (NOT isPrivate OR ownerId = :uid) AND NOT isDeleted 
	ORDER BY playerCount DESC, serverPing DESC",
	[":serverName" => $serverName, ":uid" => $uid]
);

?>
<div class="alert alert-primary" role="alert">Self hosting is NOT final. Read <a href="/games/help">here.</a></div>
<?php if (SESSION["gameban"]) { ?>
	<div class="alert alert-danger" role="alert">Your account has been game banned. You cannot host/join any server.</div>
<?php } ?>
<div class="row px-2">
	<div class="col-lg-3 px-2 mt-3">
		<div class="card">
			<div class="card-body">
				<a href="https://setup.banland.xyz/MercuryPlayerLauncher.exe" type="button" data-toggle="tooltip" data-placement="top" title="Completely safe - <?=SITE_CONFIG["site"]["name"]?> 2013." class="btn btn-block btn-success">Download Client</a>
				<a href="/games/new" type="button" data-toggle="tooltip" data-placement="top" title="It's easy to get started!" class="btn btn-block btn-info">Host Server <span class="badge badge-pill badge-warning">alpha</span></a>
				<small><a href="/help/hosting">Hosting help</a></small>
			</div>
		</div>
	</div>
	<div class="col-lg-9 mt-3">
		<div id="games" class="row">
			<div class="col-12 px-2">
				<form class="mb-3 d-flex" method="post">
					<input aria-controls="bannerTable" id="search" name="search" value="<?= $_POST['search'] ?? '' ?>" placeholder="Search for a game" class="form-control w-100" type="search">
					<button type="submit" class="btn btn-secondary d-inline ml-2 px-4"><i class="fas fa-search"></i></button>
					<a type="button" href="/games" class="btn btn-danger d-inline ml-2 px-4"><i class="fas fa-redo-alt"></i></a>
				</form>
			</div>
		<?php if ($games->rowCount()) { ?>
			<?php while ($game = $games->fetch(PDO::FETCH_OBJ)) { ?>
				<div class="col-md-4 px-2 pb-3">
					<a href="/games/view?id=<?=$game->id?>" class="text-reset text-decoration-none">
					</a>
					<div class="card hover-shadow">
						<a href="/games/view?id=<?=$game->id?>" class="text-reset text-decoration-none">
						</a>
						<div class="card-body">
							<a href="/games/view?id=<?=$game->id?><?=$game->ownerId === SESSION["userId"] && $game->isPrivate ? "&privateKey=$game->privateKey" : ""?>" class="text-reset text-decoration-none">
								<h5 style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($game->serverName)?></h5>
							</a>
							<hr>
							<small>By: <a href="/user?id=<?=$game->ownerId?>"><?=$game->username?></a></small>
							<br>
							<small>Status: <span class="badge badge-<?=$game->serverOnline?"success":"danger"?>"><?=$game->serverOnline?"Active":"Offline"?></span></small>
							<br>
							<small>Playing: <?=(int)$game->playerCount?>/<?=$game->serverSize?></small>
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
</div>
<?php pageBuilder::buildFooter(); ?>