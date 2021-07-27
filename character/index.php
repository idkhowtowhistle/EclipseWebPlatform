<?php
require $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
pageBuilder::buildHeader();
?>
<div class="row">
   <div class="col-md-4 w-75 h-75">
      <a href="#" class="btn btn-block text-white rounded-0" style="background-color: #d065d0; border-color: #d065d0; border-bottom: 0;"><i class="fas fa-sync-alt"></i> Regenerate</a>
      <div class="card rounded-0" style="border-top: 0;">
         <div class="card-body">
            <img src="https://chef.pizzaboxer.xyz/thumbs/avatar?id=5&amp;x=352&amp;y=352" class="avatar card-img">
         </div>
      </div>
      <br>
      <h3 class="font-weight-normal">Colors:</h3>
      <div class="card rounded-0">
         <div class="card-body">
         <div class="Mannequin text-center">
			<p>Click a body part to change its color:</p>
			<div class="ColorChooserFrame mx-auto" style="height:240px;width:194px;text-align:center;">
				<div style="position: relative; margin: 11px 4px; height: 1%;">
					<div style="position: absolute; left: 72px; top: 0px; cursor: pointer">
						<div class="ColorChooserRegion" data-body-part="Head" style="background-color:#E5E4DF;height:44px;width:44px;"></div>
					</div>
					<div style="position: absolute; left: 0px; top: 52px; cursor: pointer">
						<div class="ColorChooserRegion" data-body-part="Right Arm" style="background-color:#E5E4DF;height:88px;width:40px;"></div>
					</div>
					<div style="position: absolute; left: 48px; top: 52px; cursor: pointer">
						<div class="ColorChooserRegion" data-body-part="Torso" style="background-color:#635F62;height:88px;width:88px;"></div>
					</div>
					<div style="position: absolute; left: 144px; top: 52px; cursor: pointer">
						<div class="ColorChooserRegion" data-body-part="Left Arm" style="background-color:#E5E4DF;height:88px;width:40px;"></div>
					</div>
					<div style="position: absolute; left: 48px; top: 146px; cursor: pointer">
						<div class="ColorChooserRegion" data-body-part="Left Leg" style="background-color:#6E99CA;height:88px;width:40px;"></div>
					</div>
					<div style="position: absolute; left: 96px; top: 146px; cursor: pointer">
						<div class="ColorChooserRegion" data-body-part="Right Leg" style="background-color:#6E99CA;height:88px;width:40px;"></div>
					</div>
				</div>
			</div>
		</div>
         </div>
      </div>
   </div>
   <div class="col-8">
   <div class="card w-100">
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
         </div>
      </div>
   </div>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>