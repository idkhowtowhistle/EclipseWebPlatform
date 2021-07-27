<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

if(!SESSION) redirect("/");
if(SESSION["adminLevel"] < 2) redirect("/dashboard");

pageBuilder::buildHeader();



$query = $pdo->prepare("SELECT * FROM banners ORDER BY id DESC");
$query->execute();

$banners = $pdo->prepare("SELECT COUNT(*) FROM banners");
$banners->execute();
$totalBanners = $banners->fetchColumn();

?>

<div class="app py-4 container">
   <h3 class="mb-4 font-weight-normal">Site Banners</h3>
   <div class="row py-4">
      <div class="col-2">
         <div class="list-group" id="list-tab" role="tablist">
            <a aria-selected="true" aria-controls="admin" role="tab" href="#list-admin" data-toggle="list" id="list-admin-list" class="list-group-item list-group-item-action active">Add Banner</a>
            <a class="list-group-item list-group-item-action" id="list-mod-list" data-toggle="list" href="#list-mod" role="tab" aria-controls="mod" aria-selected="false">Banners</a>
         </div>
      </div>
      <div class="col-10">
         <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade active show" id="list-admin" role="tabpanel" aria-labelledby="list-admin-list">
               <div class="row pb-4">
                  <div class="col-md-7">
                        <div class="row mb-3">
                           <div class="col">
                              <label for="subject" class="form-label">Banner Color</label>
                              <input type="color" class="form-control form-control-color" style="max-width: 20rem;" id="banner-color" value="#563d7c" title="Choose your color">
                           </div>
                           <div class="col">
                              <label for="subject" class="form-label">Text Color</label>
                              <select class="form-control" id="text-color">
                                 <option value="light" selected="">Light</option>
                                 <option value="dark">Dark</option>
                              </select>
                           </div>
                        </div>
                        <div class="form-group">
                           <label for="bannerText" class="form-label">Body</label>
                           <textarea type="text" class="form-control" id="bannerText" name="bannerText" placeholder="128 characters max" rows="6" tabindex="2"></textarea>
                        </div>
                        <button class="btn btn-success btn-lg btn-block" data-control="addBanner" tabindex="4"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Add Banner</button>
                  <script>
  $('button[data-control="addBanner"]').on('click', this, function()
  {
  	var button = this; 
    $(button).attr("disabled", "disabled").find("span").show();
    $.post('/api/admin/addBanner', {"color":$("#banner-color").val(), "text":$("#bannerText").val(), "textColor":$("#text-color").val()}, function(data)
    {
      if(data.success){ toastr["success"](data.message); }
      else{ toastr["error"](data.message); }
      $(button).removeAttr("disabled").find("span").hide();
    });
  });
                  </script>
                  </div>
                  <div class="col-md-5">
                     <div class="card">
                        <div class="card-header bg-primary text-white">Banners</div>
                        <div class="card-body">
                           <p class="mb-2">Banners are the things that will appear at the top of the page, they will provide people small updates or news about the site.</p>
                           <p class="mb-1">Some small rules you should take note of before creating a banner:</p>
                           <li>Do not shitpost on the banners</li>
                           <li>Only use the banners for website updates</li>
                           <li><b>Don't abuse this, not gonna say it twice</b></li>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="tab-pane fade" id="list-mod" role="tabpanel" aria-labelledby="list-mod-list">
               <table class="table table-hover" id="bannerTable">
                  <thead class="table-bordered bg-light">
                     <tr>
                        <th class="font-weight-normal py-2" scope="col" style="width:5">ID</th>
                        <th class="font-weight-normal py-2" scope="col" style="width:5">User</th>
                        <th class="font-weight-normal py-2" scope="col" style="width: 60%;">Banner Text</th>
                        <th class="font-weight-normal py-2" scope="col" style="width: 15%;">Status</th>
                        <th class="font-weight-normal py-2" scope="col" style="width: 25%;">Date</th>
                     </tr>
                  </thead>
                  <tbody>
                  <?php while($row = $query->fetch(PDO::FETCH_OBJ)) { ?>
                     <tr>
                        <td><?=$row->id?></td>
                        <td><?=$row->username?></td>
                        <td><?=$row->message?></td>
                        <td><?=$row->active ? "Acti" : "Deacti"?>vated</td>
                        <td><?=date("n/j/Y - g:i A", $row->date)?></td>
                     </tr>
                     <?php } ?>
                  </tbody>
               </table>
               <div class="card">
                <div class="card-body">
                <div class="row my-4">
   <div class="col-lg-2 pr-1">
      <input type="number" min="1" max="<?=$totalBanners?>" placeholder="Banner ID" class="form-control" name="bannerId" id="bannerId" value="">  
   </div>
   <div class="col-lg-2 pr-1">
      <select class="form-control" name="banner-update" id="banner-update">
         <option value="1" selected="">Activate</option>
         <option value="0">Deactivate</option>
      </select>
   </div>
   <div class="col-lg-2">
      <button type="submit" id="update" name="update" data-control="updateBanner" class="btn btn-success btn-block"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Update</button>
   </div>
</div>
</div>

                </div>
               </div>
               <script>
            $(document).ready(function () {
                //Pagination full
                $('#bannerTable').DataTable({
                "pagingType": "simple_numbers",
	            "paging": true
                });
            });
             </script>
           <script>
  $('button[data-control="updateBanner"]').on('click', this, function()
  {
  	var button = this; 
    $(button).attr("disabled", "disabled").find("span").show();
    $.post('/api/admin/updateBanner', {"bannerId":$("#bannerId").val(), "active":$("#banner-update").val()}, function(data)
    {
      if(data.success){ toastr["success"](data.message); }
      else{ toastr["error"](data.message); }
      $(button).removeAttr("disabled").find("span").hide();
    });
  });
                  </script>
            </div>
         </div>
      </div>
   </div>
</div>

<?php pageBuilder::buildFooter(); ?>