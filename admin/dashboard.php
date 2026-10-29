<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if(!SESSION || SESSION["adminLevel"] < 2) pageBuilder::errorCode(404);

$query = $pdo->query("SELECT * FROM audit ORDER BY date ASC");

pageBuilder::$pageConfig["title"] = "Admin";
pageBuilder::buildHeader();
?>
<div class="app py-4 container">
    <div class="alert alert-danger py-2 text-center">Do not abuse this admin system, every action is logged whether it is moderation or administration. Abusing will result in demotion.</div>
    <h3 class="my-4 font-weight-normal"><?=SITE_CONFIG["site"]["name"]?> Admin Panel</h3>
    <div class="row py-4">
        <div class="col-2">
            <div class="list-group" id="list-tab" role="tablist">
                <a aria-selected="true" aria-controls="admin" role="tab" href="#list-admin" data-toggle="list" id="list-admin-list" class="list-group-item list-group-item-action active">Administration</a>
                <a class="list-group-item list-group-item-action" id="list-mod-list" data-toggle="list" href="#list-mod" role="tab" aria-controls="mod" aria-selected="false">Moderation</a>
                <a class="list-group-item list-group-item-action" id="list-stats-list" data-toggle="list" href="#list-stats" role="tab" aria-controls="stats" aria-selected="false">Statistics</a>
                <a aria-selected="false" aria-controls="audits" role="tab" href="#list-audits" data-toggle="list" id="list-audits-list" class="list-group-item list-group-item-action">Audit Logs</a>
            </div>
        </div>
        <div class="col-10">
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade active show" id="list-admin" role="tabpanel" aria-labelledby="list-admin-list">
                    <div class="row">
                        <div class="col-lg-6 mb-2">
                            <a href="/admin/site-banners" class="text-reset text-decoration-none">
                                <div class="card hover-shadow w-100">
                                    <div class="card-body text-center">
                                        <h1 class="display-4 my-4"><i class="fas fa-bullhorn"></i></h1>
                                        <h3 class="font-weight-normal my-4">Site Banners</h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <a href="/admin/site-manage" class="text-reset text-decoration-none">
                                <div class="card hover-shadow w-100">
                                    <div class="card-body text-center">
                                        <h1 class="display-4 my-4"><i class="far fa-window-maximize"></i></h1>
                                        <h3 class="font-weight-normal my-4">Site Management</h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 mt-2">
                            <a href="/admin/news" class="text-reset text-decoration-none">
                                <div class="card hover-shadow w-100">
                                    <div class="card-body text-center">
                                        <h1 class="display-4 my-4"><i class="far fa-newspaper"></i></h1>
                                        <h3 class="font-weight-normal my-4">News</h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 mt-2">
                            <a href="/admin/invite-keys" class="text-reset text-decoration-none">
                                <div class="card hover-shadow w-100">
                                    <div class="card-body text-center">
                                        <h1 class="display-4 my-4"><i class="fas fa-key"></i></h1>
                                        <h3 class="font-weight-normal my-4">Invite Keys</h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-mod" role="tabpanel" aria-labelledby="list-mod-list">
                    <div class="row">
                        <div class="col-lg-6 mb-2">
                            <a href="/admin/moderation" class="text-reset text-decoration-none">
                                <div class="card hover-shadow w-100">
                                    <div class="card-body text-center">
                                        <h1 class="display-4 my-4"><i class="fas fa-gavel"></i></h1>
                                        <h3 class="font-weight-normal my-4">User Moderation</h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <div class="card hover-shadow w-100">
                                <div class="card-body text-center">
                                    <h1 class="display-4 my-4"><i class="fas fa-book"></i></h1>
                                    <h3 class="font-weight-normal my-4">Asset Approval</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-2">
                            <a href="/admin/user-manage" class="text-reset text-decoration-none">
                                <div class="card hover-shadow w-100">
                                    <div class="card-body text-center">
                                        <h1 class="display-4 my-4"><i class="fas fa-users"></i></h1>
                                        <h3 class="font-weight-normal my-4">User Management</h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 mt-2">
                            <a href="/admin/reports" class="text-reset text-decoration-none">
                                <div class="card hover-shadow w-100">
                                    <div class="card-body text-center">
                                        <h1 class="display-4 my-4"><i class="fas fa-exclamation-circle"></i></h1>
                                        <h3 class="font-weight-normal my-4">Reports</h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-stats" role="tabpanel" aria-labelledby="list-stats-list">
                    <div class="row">
                        <div class="col-lg-6 mb-2">
                            <div class="card w-100">
                                <div class="card-body text-center">
                                    <h1 class="display-4 my-4"><i class="far fa-user mr-1"></i></h1>
                                    <h3 class="font-weight-normal my-4">Users online: <?= users::getUsersOnline();?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <div class="card w-100">
                                <div class="card-body text-center">
                                    <h1 class="display-4 my-4"><i class="fas fa-server"></i></h1>
                                    <h3 class="font-weight-normal my-4">Assets created: <?=upload::GetAllAssets();?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-2">
                            <div class="card w-100">
                                <div class="card-body text-center">
                                    <h1 class="display-4 my-4"><i class="fas fa-users"></i></h1>
                                    <h3 class="font-weight-normal my-4">
                                        Registered users: <?=users::getAllUsers()?>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-2">
                            <div class="card w-100">
                                <div class="card-body text-center">
                                    <h1 class="display-4 my-4"><i class="far fa-hdd"></i></h1>
                                    <h3 class="font-weight-normal my-4"> <?=general::getFileSize(disk_total_space("/")-disk_free_space("/"))?> / <?=general::getFileSize(disk_total_space("/var/www/accounts/multako/banland.xyz/"))?> Used</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-audits" role="tabpanel" aria-labelledby="list-audits-list">
                    <table class="table table-hover" id="auditlist">
                        <thead class="table-bordered bg-light">
                            <tr>
                                <th class="font-weight-normal py-2" scope="col" style="width:15">ID</th>
                                <th class="font-weight-normal py-2" scope="col" style="width:15">User</th>
                                <th class="font-weight-normal py-2" scope="col" style="width:15">Moderator</th>
                                <th class="font-weight-normal py-2" scope="col" style="width:15">Type</th>
                                <th class="font-weight-normal py-2" scope="col" style="width:15%">Option</th>
                                <th class="font-weight-normal py-2" scope="col" style="width:15%">Action</th>
                                <th class="font-weight-normal py-2" scope="col" style="width:15">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = $query->fetch(PDO::FETCH_OBJ)) { ?>
                            <tr>
                                <td><?=$row->id?></td>
                                <td><?=users::getUsernameFromUid($row->userIdModerated)?></td>
                                <td><?=users::getUsernameFromUid($row->moderatorId)?></td>
                                <td><?=$row->type?></td>
                                <td><?=$row->modoption?></td>
                                <td>
                                    <button type="button" data-control="viewAction" data-text="<?=$row->action?>" data-title="Audit Log #<?=$row->id?> - <?=$row->modoption?>" class="btn btn-info btn-sm btn-block">Click Here
                                    </button>
                                    
                                </td>
                                <td><?=date('j/n/Y g:i:s A \G\M\T', $row->date)?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

$(document).ready(function () {
                //Pagination full
                $('#auditlist').DataTable({
                "pagingType": "simple_numbers",
	            "paging": true
                });
            });
  $('button[data-control="viewAction"]').on('click', this, function()
  {
    showModal($(this).attr("data-title"), $(this).attr("data-text"), [{'class':'btn btn-outline-success', 'isDismissButton':true, 'text':'Close'}]);
  });
</script>
<?php pageBuilder::buildFooter()?>