<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/private/core.php';
users::requireLogin();
pageBuilder::buildHeader();
?>
<div class="app py-4 container">
   <div class="row">
      <div class="col-sm-2">
         <button type="button" class="btn btn-outline-primary btn-block"><i class="fas fa-plus"></i> New Asset</button>
         <div class="card mt-3">
            <div class="card-header">Type</div>
            <div class="card-body">
               <button type="button" class="btn btn-primary btn-block">Hats</button><button type="button" class="btn btn-light btn-block">T-Shirts</button><button type="button" class="btn btn-light btn-block">Shirts</button><button type="button" class="btn btn-light btn-block">Pants</button><button type="button" class="btn btn-light btn-block">Gears</button><button type="button" class="btn btn-light btn-block">Faces</button><button type="button" class="btn btn-light btn-block">Heads</button><button type="button" class="btn btn-light btn-block">Models</button><button type="button" class="btn btn-light btn-block">Decals</button><button type="button" class="btn btn-light btn-block">Audio</button>
            </div>
         </div>
      </div>
      <div class="col-sm-10">
         <p class="mb-3 d-flex">
            <input aria-controls="bannerTable" placeholder="Search for an item" class="form-control w-100" type="search">
            <select class="form-control ml-2 col-lg-2 categoriesForKeyword">
               <option value="Custom" selected="selected">Hats</option>
               <option value="1">All Categories</option>
               <option value="3" selected="">Clothing</option>
               <option value="4">Body Parts</option>
               <option value="5">Gear</option>
               <option value="6">Models</option>
               <option value="8">Decals</option>
               <option value="9">Audio</option>
            </select>
            <button type="button" class="btn btn-secondary ml-2 px-4">Search</button>
         </p>
         <div class="row">
            <div class="col-lg-2 mb-2">
               <div class="card hover-shadow">
                  <div class="card-body">
                     <p class="text-center"><img class="img-fluid" src="https://media.istockphoto.com/photos/traffic-cone-picture-id504567615?k=6&amp;m=504567615&amp;s=170667a&amp;w=0&amp;h=8dg2yif2eSK74tmJOV-b7Gk-976r2Kgo_ytjTTwnORc="></p>
                     <a href="#" class="m-0">Traffic Cone</a>
                     <p class="m-0 text-success"><i class="far fa-money-bill-alt"></i> 2000</p>
                  </div>
               </div>
            </div>
            <div class="col-lg-2 mb-2">
               <div class="card hover-shadow">
                  <div class="card-body">
                     <p class="text-center"><img class="img-fluid" src="https://media.istockphoto.com/photos/traffic-cone-picture-id504567615?k=6&amp;m=504567615&amp;s=170667a&amp;w=0&amp;h=8dg2yif2eSK74tmJOV-b7Gk-976r2Kgo_ytjTTwnORc="></p>
                     <a href="#" class="m-0">Traffic Cone</a>
                     <p class="m-0 text-success"><i class="far fa-money-bill-alt"></i> 2000</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="pagination mt-3 form-inline justify-content-center">
      <button type="button" class="btn btn-light mx-3 back" disabled="">
         <h5 class="mb-0"><i class="fas fa-chevron-left"></i></h5>
      </button>
      <span>Page</span>
      <input value="1" style="width:30px" type="text" class="form-control form-control-sm text-center mx-1 page">
      <span>of 1</span>
      <button type="button" class="btn btn-light mx-3 next" disabled="">
         <h5 class="mb-0"><i class="fas fa-chevron-right"></i></h5>
      </button>
   </div>
</div>
<?php pageBuilder::buildFooter(); ?>