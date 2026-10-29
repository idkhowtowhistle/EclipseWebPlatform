<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

pageBuilder::$pageConfig["title"] = "Status";
pageBuilder::buildHeader();
?>
<style>
.coolhr {
    margin: 0;
    margin-bottom: 10px;
    border: none;
    width: 400px;
    height: 1px;
    opacity: 0.5;
    background: linear-gradient( to right, red 20%, yellow 20%, yellow 36%, green 36%, green 60%, blue 60%, blue 100% );
}
</style>
<div class="app py-4 container">
   <h4 class="font-weight-normal"><?=SITE_CONFIG["site"]["name"]?> Status</h4>
   <hr class="coolhr">
   <div class="form-check">
      <div class="custom-control custom-checkbox">
         <input type="checkbox" class="custom-control-input" id="customCheck1" checked disabled>
         <label class="custom-control-label" for="customCheck1">Web server</label>
      </div>
   </div>
   <div class="form-check">
      <div class="custom-control custom-checkbox">
         <input type="checkbox" class="custom-control-input" id="customCheck1"checked disabled>
         <label class="custom-control-label" for="customCheck1">Render server</label>
      </div>
   </div>
   <hr>
   <div class="row">
   <div class="col">
   <div class="form-group">
      <p>Client Progress</p>
      <div class="progress" style="width: 100%;">
         <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="50" style="width: 80%"></div>
      </div>
   </div>
   </div>
   <div class="col">
   <div class="form-group">
      <p>Site Progress</p>
      <div class="progress" style="width: 100%;">
         <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="50" style="width: 45%"></div>
      </div>
   </div>
   </div>
   </div>
   <hr>
   <h4 class="font-weight-normal">
   Your internet is working
   </h4>
</div>
<?php pageBuilder::buildFooter(); ?>