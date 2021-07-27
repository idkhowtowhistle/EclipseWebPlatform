<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

if(!SESSION) {
    redirect("/");
}

pageBuilder::buildHeader();
?>
<br>
<div class="app py-4 container">
   <div class="container">
    <div class="row">
        <div class="col">
        <div class="container">
        <h2>ITEM_NAME - ITEM_TYPE</h2>
        <img src="<?=users::getAvatar($userInfo->id)?>" class="my-2" alt="Player" height="300" width="300">
        </div>
        </div>
        <div class="col">
        <div class="card">
            <div class="card-body" style="height: 100%">
            <h3>ITEM_NAME by <a  href="#">taskmanager</a></h3>
            <p>Type: Shirt</p>
            <hr>
            <p>DESC_HERE Lorem ipsum HEHEHEHEH</p>
            <hr>
            <button type="button" class="btn btn-success btn-lg btn-block">Free</button>
            </div>
        </div>
        </div>
    </div>
   <div class="card mt-3 shadow-sm rounded-0">
            <div class="card-body text-center">
               <div class="row">
                  <div class="col-lg-4">
                     <h5>Created On: </h5>
                     TIME_STAMP
                  </div>
                  <div class="col-lg-4">
                     <h5>Updated On: </h5>
                     TIME_STAMP
                  </div>
                  <div class="col-lg-4">
                     <h5>Owned: </h5>
                     OWNERS
                  </div>
               </div>
            </div>
         </div>
   </div>
</div>
<?php
pageBuilder::buildFooter();
?>