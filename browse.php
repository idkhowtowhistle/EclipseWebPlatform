<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();


$query = $pdo->prepare("SELECT * FROM users WHERE NOT (SELECT COUNT(*) FROM bans WHERE userId = users.id AND NOT undone) ORDER BY lastonline DESC");
$query->execute();

pageBuilder::buildHeader();
?>
<div class="app py-4 container">
	<table class="table table-hover" id="usersTable">
		<thead class="table-bordered bg-light">
			<tr>
				<th class="font-weight-normal py-2" scope="col" style="width:3%">ID</th>
				<th class="font-weight-normal py-2" scope="col" style="width:3%">Avatar</th>
				<th class="font-weight-normal py-2" scope="col" style="width:5%">Username</th>
				<th class="font-weight-normal py-2" scope="col" style="width:20%">Blurb</th>
				<th class="font-weight-normal py-2" scope="col" style="width:7%">Location / Last Seen</th>
			</tr>
		</thead>
		<tbody>
			<?php while ($row = $query->fetch(PDO::FETCH_OBJ)) {
				$onlineInfo = users::getOnlineStatus($row->id) ?>
				<tr>
					<td><?=$row->id?></td>
					<td><a href="/user?id=<?=$row->id?>"><img src="/thumbnails/avatar?id=<?=$row->id?>&x=75&y=75" title="<?=$row->username?>" alt="<?=$row->username?>" class="img-fluid"></a></td>
					<td><a href="/user?id=<?= $row->id ?>"><?= $row->username ?></a></td>
					<td><?= htmlspecialchars($row->blurb) ?></td>
					<td style="<?= $onlineInfo["online"] ? 'color:green;' : 'color:grey;' ?>"><?= $onlineInfo["online"] ? "Online @ " . $onlineInfo["text"] : "Offline @ " . date("n/j/Y", $row->lastonline) ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>

<script>
	$(document).ready(function() {
		//Pagination full
		$('#usersTable').DataTable({
			"pagingType": "simple_numbers",
			"paging": true
		});
	});
</script>
<?php pageBuilder::buildFooter(); ?>