<?php
	require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
	users::requireLogin();
	
	$gameId = $_GET["id"] ?? false;
	$privateKey = $_GET["privateKey"] ?? false;
	$gameInfo = games::getGameInfoFromId($gameId, true);
	
	if (!$gameInfo || $gameInfo->isDeleted) pageBuilder::errorCode(404);
	if ($gameInfo->isPrivate && $privateKey !== $gameInfo->privateKey) pageBuilder::errorCode(404);
	
	$players = games::getPlayersInServer($gameInfo->id);
	$hostScriptUrl = "http://banland.xyz/Game/Gameserver.ashx?ticket=".$gameInfo->serverTicket;
	
	pageBuilder::buildHeader();
?>
<div class="alert alert-danger" role="alert">Make sure you have read the <a href="/games/help">guide</a> before joining and hosting games.</div>
<?php if (SESSION["gameban"]) { ?>
<div class="alert alert-danger" role="alert">Your account has been game banned. You cannot host/join any server.</div>
<?php } ?>
<div class="container">
	<div class="row">
		<div class="col-sm-8">
			<div class="card">
				<div class="card-body"><?= htmlspecialchars($gameInfo->serverDesc) ?></div>
			</div>
			<br>
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-lg-3">
							<b>Server Size</b>
							<p class="mb-0"><?= $gameInfo->serverSize ?></p>
						</div>
						<div class="col-lg-3">
							<b class="mb-0">Created On</b>
							<p class="mb-0"><?= date("n/j/Y", $gameInfo->createdOn) ?></p>
						</div>
						<div class="col-lg-3">
							<b class="mb-0">Last Updated</b>
							<p class="mb-0"><?= date("n/j/Y", $gameInfo->lastUpdated) ?></p>
						</div>
						<div class="col-lg-3">
							<b>Visits</b>
							<p class="mb-0"><?= $gameInfo->visitCount ?></p>
						</div>
					</div>
				</div>
			</div>
			<?php if (SESSION["userId"] === $gameInfo->ownerId || SESSION["adminLevel"] > 2) { ?>
			<hr>
			<h4 class="font-weight-normal">Now it's time to start hosting!</h4>
			<p>First things first, you'll need to port forward the port you selected for this server. If you're not sure how, see our <a href="/games/portforwarding">guide to port forwarding</a>.</p>
			<p class="mb-0">With that out of the way, you should get your server running. There's two ways to host - each panel tells you what exactly they do. We recommend using Autopilot.</p>
			<div class="row py-4">
		        <div class="col-sm-3">
		            <div class="list-group" id="list-tab" role="tablist">
		                <a class="list-group-item list-group-item-action active" id="autopilot-tab" data-toggle="tab" href="#autopilot" role="tab" aria-controls="autopilot" aria-selected="true">Autopilot</a>
		                <a class="list-group-item list-group-item-action" id="manual-tab" data-toggle="tab" href="#manual" role="tab" aria-controls="manual" aria-selected="false">Manual</a>
		            </div>
		        </div>
		        <div class="col-sm-9">
		            <div class="tab-content" id="nav-tabContent">
		                <div class="tab-pane fade active show" id="autopilot" role="tabpanel" aria-labelledby="autopilot-tab">
		                	<p>Autopilot handles most of the initial studio stuff for you. All you need to do is type in a map that's in the map folder, and start the server.</p>
		                	<p>Place your maps in <a href="#" onclick="mercury_games.launch('mercury-player:1+launchmode:maps')">Mercury Studio's maps folder</a>. For example, entering <code>CoolMap.rbxl</code> will point to <code>content\maps\CoolMap.rbxl</code>.</p>
		                	<div class="input-group mb-3">
								<input type="text" class="filepath form-control" placeholder="CoolMap.rbxl">
								<div class="input-group-append">
									<div class="btn-group">
										<button type="button" class="btn btn-success" onclick="mercury_games.launch('mercury-player:1+launchmode:ide+script:<?=$hostScriptUrl?>&autopilot=' + btoa($('.filepath').val()))">Start Server</button>
										<button type="button" class="btn btn-outline-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="sr-only">Toggle Dropdown</span>
										</button>
										<div class="dropdown-menu dropdown-menu-right">
											<!--a class="dropdown-item" href="#">Start Server (No Graphics Rendering)</a-->
											<a class="dropdown-item" href="#" onclick="mercury_games.launch('mercury-player:1+launchmode:build+script:<?=$hostScriptUrl?>&autopilot=' + btoa($('.filepath').val()))">Start Server (No Studio Tools)</a>
										</div>
									</div>
								</div>
							</div>             
						</div>
		                <div class="tab-pane fade" id="manual" role="tabpanel" aria-labelledby="manual-tab">
		                	<p>Manual is the standard way of self hosting that you've probably seen before. We prefer using Autopilot if you want a quicker and easier way of hosting.</p>
		                	<p class="mb-0">Open studio and open your map. Once you've done that, paste this into the command bar:</p>
		                	<code>loadfile('<?=$hostScriptUrl?>')()</code>
		                </div>
		            </div>
		        </div>
		    </div>
			<?php } ?>
		</div>
		<div class="col-sm-4">
			<div class="card">
				<div class="card-body">
					<h4 class="font-weight-normal"><?= htmlspecialchars($gameInfo->serverName) ?></h4>
					<span>By: <a href="/user?id=<?= $gameInfo->ownerId ?>"><?= $gameInfo->username ?></a></span>
					<br>
					<span>Gears: <i class="fas fa-bomb"></i></span>
					<br>
					<span>Status: <span class="badge badge-<?= ($gameInfo->serverOnline ? "success" : "danger") ?>"><?= ($gameInfo->serverOnline ? "Active" : "Offline") ?></span></span>
				</div>
			</div>
			<hr>
			<?php if (SESSION["userId"] === $gameInfo->ownerId || SESSION["adminLevel"] > 2) { ?>
			<div class="row">
				<div class="col-lg-9">
					<div class="btn-group btn-block">
						<button type="button" class="btn btn-block btn-sm btn-info" onclick="mercury_games.launch('mercury-player:1+launchmode:ide')">Open Studio</button>
					</div>
				</div>
				<div class="col">
					<div class="btn-group">
						<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fas fa-cogs"></i>
						</button>
						<div class="dropdown-menu">
							<a class="dropdown-item" href="/games/edit?id=<?= $gameInfo->id ?>">Configure Server</a>
						</div>
					</div>
				</div>
			</div>
			<hr>
			<?php } ?>
			<button type="button" data-control="join-server" data-server-key="<?= $gameInfo->isPrivate ? "$privateKey" : "false" ?>" data-server-id="<?= $gameInfo->id ?>" class="btn btn-block rounded-0 btn-success join-server" <?= SESSION["gameban"] ? "disabled" : "" ?>><i class="fas fa-play"></i> Play</button>
			<div class="place-launcher mt-3">
				<p class="place-launcher-success" style="display:none;"><i class="fas fa-check-circle text-success"></i> Started Client!</p>
				<p class="place-launcher-error" style="display:none;"><i class="fas fa-times-circle text-danger"></i></p>
			</div>
		</div>
	</div>
	<hr>
	<div class="card">
		<div class="card-body">
			<?php if ($players->rowCount()) { ?>
			<div class="row">
				<div class="col-1 divider-right">
					<p>Playing: <?=$players->rowCount()?>/<?=$gameInfo->serverSize?></p>
				</div>
				<?php while ($player = $players->fetch(PDO::FETCH_OBJ)) { ?>
				<div class="col-1">
					<img data-toggle="tooltip" data-placement="top" title="<?=$player->username?>" src="/thumbnails/avatar?id=<?=$player->id?>&x=75&y=75" title="<?= $player->username ?>" width="64" height="64">
				</div>
				<?php } ?>
			</div>
			<?php } else { ?>
			There are no current players
			<?php } ?>
		</div>
	</div>
</div>
<!-- modal -->
<div class="modal fade" id="place-launcher-install" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Install</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				It seems like you dont have the <?=SITE_CONFIG["site"]["name"]?> Client installed. If you do, just ignore this message.
				<hr>
				<a class="btn btn-success btn-block" href="https://setup.banland.xyz/MercuryPlayerLauncher.exe" role="button">Install</a>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script src="/js/serverLaunch.js<?= '?t=' . time() ?>"></script>
<script>
	$("button[data-control$='join-server']").on("click", function() {
	  mercury_games.join_server($(this).attr("data-server-id"), $(this).attr("data-server-key"));
	});
</script>
<?php pageBuilder::buildFooter(); ?>