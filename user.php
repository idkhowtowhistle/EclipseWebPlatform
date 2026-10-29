<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();

$requestPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
if (preg_match("#^/users?/([0-9]+)/?$#", $requestPath, $routeMatch)) $userId = (int)$routeMatch[1];
else if (isset($_GET["id"])) 
{
	if (ctype_digit((string)$_GET["id"])) 
	{
		header("Location: /user/".(int)$_GET["id"], true, 301);
		exit;
	}
	$userId = $_GET["id"];
}
else if (SESSION) $userId = SESSION["userId"];
else $userId = false;

$userInfo = users::getUserInfoFromUid($userId);
if (!$userInfo || users::getUserModeration($userId)) pageBuilder::errorCode(404);

$onlineInfo = users::getOnlineStatus($userInfo->id);
$userGames = games::getGamesFromUser($userInfo->id);

if(SESSION && SESSION["adminLevel"] > 1) 
{
	$alts = [];
	
	function recurseAlts($userID)
	{
		global $alts;
		$altLookup = db::run(
			"SELECT users.username, userId, users.joined, sessions.ip FROM mercury.sessions 
			INNER JOIN mercury.users ON users.id = userId 
			WHERE sessions.ip IN (SELECT sessions.ip FROM mercury.sessions WHERE userId = :id) 
			AND userId != :id GROUP BY userId",
			[":id" => $userID]
		);

		while($alt = $altLookup->fetch(PDO::FETCH_OBJ)) 
		{
			$ipParts = explode(".", $alt->ip);

			$alts[] = 
			[
				"username" => $alt->username, 
				"userid" => $alt->userId, 
				"created" => $alt->joined, 
				"ip" => $alt->ip,
				"ipcensored" => $ipParts[0].".".$ipParts[1].".xxx.xxx"
			];
		}
	}

	recurseAlts($userInfo->id);
}

pageBuilder::$pageConfig["title"] = htmlspecialchars($userInfo->username);
pageBuilder::buildHeader();
?>
<div class="app py-4 container">
	<div class="mprofile mb-3">
		<div class="mprofile-head">
			<img class="mprofile-avatar" src="/thumbnails/headshot?id=<?=$userInfo->id?>&x=250&y=250" alt="<?= htmlspecialchars($userInfo->username) ?>">
			<div class="flex-grow-1" style="min-width: 0">
				<div class="mprofile-name">
					<h1><?= htmlspecialchars($userInfo->username) ?></h1>
					<span class="mprofile-rank"><?= users::getRank($userInfo->admin) ?></span>
					<?php if ($userInfo->ssc) { ?><i style="color: hsl(0 72 61)" data-toggle="tooltip" data-placement="right" title="Super Safe Chat" class="fas fa-comment-slash"></i><?php } ?>
				</div>
				<div class="mprofile-stats">
					<a href="/friends<?=$userInfo->id === SESSION["userId"] ? "" : "?id=" . $userInfo->id?>">
						<span>Friends</span>
						<h2>6</h2>
					</a>
					<div>
						<span>Rocks</span>
						<h2><?= $userInfo->currency ?></h2>
					</div>
					<div>
						<span>Visits</span>
						<h2><?= games::getTotalVisitsOfUser($userInfo->id) ?></h2>
					</div>
					<div>
						<span>Joined</span>
						<h2 style="font-size: 1.05rem; padding-top: 0.35rem"><?= date("n/j/Y", $userInfo->joined) ?></h2>
					</div>
				</div>
				<div class="mprofile-actions">
					<?php if($onlineInfo["online"] && $onlineInfo["playing"]) { $gameInfo = games::getGameInfoFromId($onlineInfo["serverid"]); ?>
						<?php if(!$gameInfo->isPrivate) { ?>
						<a class="btn btn-sm btn-success" role="button" href="/games/view?id=<?=$onlineInfo["serverid"]?>"><i class="fas fa-play mr-1"></i>Join Server</a>
						<?php } ?>
					<?php } ?>
					<?php if ($userInfo->id !== SESSION["userId"]) { ?>
						<a class="btn btn-sm btn-secondary <?= !$userInfo->messages || SESSION["ssc"] ? "disabled" : "" ?>" role="button" href="/messages/new?messageUser=<?= $userInfo->id ?>"><i class="fas fa-envelope mr-1"></i>Message</a>
						<a class="btn btn-sm btn-secondary disabled" role="button" href="#"><i class="fas fa-users mr-1"></i>Add Friend</a>
					<?php } ?>
					<?php if (SESSION["adminLevel"] >= 2 && $userInfo->id !== SESSION["userId"] && $userInfo->admin < 2) { ?>
						<a class="btn btn-sm btn-outline-danger" role="button" href="#"><i class="fas fa-gavel mr-1"></i>Moderate</a>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
   <div class="row">
      <div class="col-sm-5">
         <div class="card">
            <div class="card-body text-center">
               <div class="mb-2">
                  <h5 class="mb-1"><a href="#" class="badge badge-<?= $onlineInfo["online"] ? "success" : "secondary" ?>"><?= $onlineInfo["online"] ? "Online" : "Offline" ?></a> <small data-control="onlineStatus"><?= $onlineInfo["online"] ? $onlineInfo["text"] : "Last Online On ".date("n/j/Y", $userInfo->joined) ?></small></h5>
                  <a href="https://idkhowtowhistle.dpdns.org/user/<?= $userInfo->id ?>" class="mb-3">https://idkhowtowhistle.dpdns.org/user/<?= $userInfo->id ?></a>
               </div>
               <img src="/thumbnails/avatar?id=<?=$userInfo->id?>&x=250&y=250" class="mb-3 img-fluid" alt="Player">
               <div class="text-center overflow-auto" style="max-height: 130px">
                  <p class="text-break mb-0"><?= htmlspecialchars($userInfo->blurb) ?>
                     <?php if (SESSION["adminLevel"] > 1 && $userInfo->admin < 2) { ?>
                        <button data-control="admin-editBio" class="btn btn-light"><i class="fas fa-edit"></i></button>
                     <?php } ?>
                  </p>
               </div>
               <?php if (SESSION["adminLevel"] > 1 && $userInfo->admin < 2) { ?>
                  <div class="form-group" data-control="newBio" style="display: none;">
                     <label for="newBio">Edit User Bio</label>
                     <textarea class="form-control" id="newBio" rows="3"></textarea>
                  </div>
                  <button style="display: none;" data-control="admin-saveBio" class="btn btn-block btn-success"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Edit</button>
               <?php } ?>
            </div>
            <?php if (SESSION["adminLevel"] > 1 && $userInfo->admin < 2) { ?>
               <script>
                  $("[data-control='admin-saveBio']").on("click", this, function() {
                     var btn = this;
                     $(btn).attr("disabled", "disabled").find("span").show();
                     $.post('/api/admin/updateProfileBio', {
                        "bio": $("#newBio").val(),
                        "userId": "<?= $userInfo->id ?>"
                     }, function(data) {
                        if (data.success) {
                           toastr["success"](data.message);
                        } else {
                           toastr["error"](data.message);
                        }
                        $(btn).removeAttr("disabled").find("span").hide();
                     })
                  })

                  $("button[data-control='admin-editBio']").on("click", function() {
                     $("[data-control$='newBio']").toggle(400)
                     $("[data-control$='admin-saveBio']").toggle(400)
                  })
               </script>
            <?php } ?>
         </div>
         <?php if (SESSION["adminLevel"] >= 2) { ?>
            <div class="card mt-3">
               <div class="card-header">Alternative Accounts</div>
               <div class="card-body">
               		<?php foreach($alts as $alt) { ?>
                  	<a class="btn btn-light btn-block text-left py-1 mb-2" href="/user/<?=$alt["userid"]?>" title="<?=$alt["ipcensored"]?>" data-toggle="tooltip"><?=$alt["username"]?> <span class="float-right">[<?=date('d/m/Y h:i:s A', $alt["created"])?>]</span></a>
              		<?php } if(!count($alts)) { ?>
              		<p class="mb-0">This user does not have any alternate accounts</p>
              		<?php } ?>
               </div>
            </div>
         <?php } ?>
      </div>

      <div class="col-lg-7">
         <div class="card">
            <div class="card-header">Games</div>
            <div class="card-body">
               	<?php while ($game = $userGames->fetch(PDO::FETCH_OBJ)) { ?>
				<div class="items mb-2">
                    <div class="item mt-0">
                    	<a class="btn btn-primary rounded-0 btn-block text-left py-1" data-toggle="collapse" href="#gameCol-<?= $game->id ?>"><?= htmlspecialchars($game->serverName) ?></a>
                    </div>
                    <div class="collapse" id="gameCol-<?= $game->id ?>">
						<div class="card rounded-0">
							<div class="card-body">
								<p><?= htmlspecialchars($game->serverDesc) ?></p>
								<div class="row">
									<div class="col-5">
										<a class="btn btn-lg btn-block btn-secondary" href="/games/view?id=<?= $game->id ?>" role="button">Play</a>
									</div>
									<div class="col-2 text-center">
										<h5>Status <br> <span class="badge badge-<?= ($game->serverOnline ? "success" : "danger") ?>"><?= ($game->serverOnline ? "Active" : "Offline") ?></span></h5>
									</div> 
									<div class="col-2 text-center">
	                                    <h5>Visits <br> <?=$game->visitCount?></h5>
									</div> 
									<div class="col-3 text-center">
	                                    <h5>Playing <br> <?=(int)$game->playerCount?></h5>
									</div> 
								</div>
							</div>
						</div>
					</div>
				</div>
               <?php } if (!$userGames->rowCount()) { ?>
				<p class="text-left py-1"><?= $userInfo->username ?> doesn't have any games.</p>
               <?php } ?>
            </div>
         </div>
         <div class="card mt-3">
            <div class="card-header">
               <div class="float-left">Friends</div>
               <a href="/friends<?=$userId === SESSION["userId"] ? "" : "?id=$userId"?>" class="btn btn-primary float-right py-0">View All</a>
            </div>
            <div class="card-body">
               <div class="tab-content">
                  <div class="tab-pane friends-container active" id="friends" role="tabpanel" aria-labelledby="friends-tab">
                     <div class="loading text-center d-none"><span class="jumbo spinner-border" role="status"></span></div>
                     <p class="no-items d-none"></p>
                     <div class="items row px-2">
                        <div class="friend-card col-lg-4 col-6 px-2 my-2 text-center">
                           <div class="card hover-shadow p-2">
                              <a href="/user/4" class="text-reset text-decoration-none">
                                 <img class="mt-2 img-fluid" title="Username" alt="Multako" src="/thumbnails/avatar?id=4&x=250&y=250">
                                 <h5 class="my-2 font-weight-normal" href="/user/4">Username</h5>
                              </a>
                           </div>
                        </div>
                        <div class="friend-card col-lg-4 col-6 px-2 my-2 text-center">
                           <div class="card hover-shadow p-2">
                              <a href="/user/4" class="text-reset text-decoration-none">
                                 <img class="mt-2 img-fluid" title="Username" alt="Multako" src="/thumbnails/avatar?id=4&x=250&y=250">
                                 <h5 class="my-2 font-weight-normal" href="/user/4">Username</h5>
                              </a>
                           </div>
                        </div>
                        <div class="friend-card col-lg-4 col-6 px-2 my-2 text-center">
                           <div class="card hover-shadow p-2">
                              <a href="/user/4" class="text-reset text-decoration-none">
                                 <img class="mt-2 img-fluid" title="Username" alt="Multako" src="/thumbnails/avatar?id=4&x=250&y=250">
                                 <h5 class="my-2 font-weight-normal" href="/user/4">Username</h5>
                              </a>
                           </div>
                        </div>
                        <div class="friend-card col-lg-4 col-6 px-2 my-2 text-center">
                           <div class="card hover-shadow p-2">
                              <a href="/user/4" class="text-reset text-decoration-none">
                                 <img class="mt-2 img-fluid" title="Username" alt="Multako" src="/thumbnails/avatar?id=4&x=250&y=250">
                                 <h5 class="my-2 font-weight-normal" href="/user/4">Username</h5>
                              </a>
                           </div>
                        </div>
                        <div class="friend-card col-lg-4 col-6 px-2 my-2 text-center">
                           <div class="card hover-shadow p-2">
                              <a href="/user/4" class="text-reset text-decoration-none">
                                 <img class="mt-2 img-fluid" title="Username" alt="Multako" src="/thumbnails/avatar?id=4&x=250&y=250">
                                 <h5 class="my-2 font-weight-normal" href="/user/4">Username</h5>
                              </a>
                           </div>
                        </div>
                        <div class="friend-card col-lg-4 col-6 px-2 my-2 text-center">
                           <div class="card hover-shadow p-2">
                              <a href="/user/4" class="text-reset text-decoration-none">
                                 <img class="mt-2 img-fluid" title="Username" alt="Multako" src="/thumbnails/avatar?id=4&x=250&y=250">
                                 <h5 class="my-2 font-weight-normal" href="/user/4">Username</h5>
                              </a>
                           </div>
                        </div>
                     </div>
                     <div class="template d-none">
                        <div class="friend-card col-lg-4 col-6 px-2 my-2 text-center">
                           <div class="card hover p-2">
                              <a href="/user/$userid"><img class="card-img-top img-fluid" preload-src="/thumbs/avatar?id=$userid&amp;x=250&amp;y=250" title="$username" alt="$username"></a>
                              <a href="/user/$userid">$username</a>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="card mt-3">
      <div class="card-header">
         <ul class="nav nav-tabs nav-fill card-header-tabs" id="myTab" role="tablist">
            <li class="nav-item">
               <a class="nav-link" id="hats-tab" data-toggle="tab" href="#hats" role="tab" aria-controls="hats" aria-selected="false">Hats</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" id="shirts-tab" data-toggle="tab" href="#shirts" role="tab" aria-controls="shirts" aria-selected="false">Shirts</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" id="tshirts-tab" data-toggle="tab" href="#tshirts" role="tab" aria-controls="tshirts" aria-selected="false">T-Shirts</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" id="pants-tab" data-toggle="tab" href="#pants" role="tab" aria-controls="pants" aria-selected="false">Pants</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" id="gears-tab" data-toggle="tab" href="#gears" role="tab" aria-controls="gears" aria-selected="false">Gears</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" id="faces-tab" data-toggle="tab" href="#faces" role="tab" aria-controls="faces" aria-selected="false">Faces</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" id="heads-tab" data-toggle="tab" href="#heads" role="tab" aria-controls="heads" aria-selected="false">Heads</a>
            </li>
            <li class="nav-item">
               <a class="nav-link active" id="models-tab" data-toggle="tab" href="#models" role="tab" aria-controls="models" aria-selected="true">Models</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" id="decals-tab" data-toggle="tab" href="#decals" role="tab" aria-controls="decals" aria-selected="false">Decals</a>
            </li>
            <li class="nav-item">
               <a class="nav-link" id="audio-tab" data-toggle="tab" href="#audio" role="tab" aria-controls="audio" aria-selected="false">Audio</a>
            </li>
         </ul>
      </div>
      <div class="card-body">
         <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade" id="hats" role="tabpanel" aria-labelledby="hats-tab">...</div>
            <div class="tab-pane fade" id="shirts" role="tabpanel" aria-labelledby="shirts-tab">...</div>
            <div class="tab-pane fade" id="tshirts" role="tabpanel" aria-labelledby="tshirts-tab">...</div>
            <div class="tab-pane fade" id="pants" role="tabpanel" aria-labelledby="pants-tab">...</div>
            <div class="tab-pane fade" id="gears" role="tabpanel" aria-labelledby="gears-tab">...</div>
            <div class="tab-pane fade" id="faces" role="tabpanel" aria-labelledby="faces-tab">...</div>
            <div class="tab-pane fade" id="heads" role="tabpanel" aria-labelledby="heads-tab">...</div>
            <div class="tab-pane fade" id="decals" role="tabpanel" aria-labelledby="decals-tab">...</div>
            <div class="tab-pane fade active show" id="models" role="tabpanel" aria-labelledby="models-tab">...</div>
            <div class="tab-pane fade" id="audio" role="tabpanel" aria-labelledby="audio-tab">...</div>
         </div>
      </div>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>