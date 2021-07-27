<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if(!SESSION) redirect("/");
pageBuilder::buildHeader();
?>
<div class="app py-4 container">
   <h3 class="mb-2 font-weight-normal">New Asset</h3>
   <p class="mb-4 text-muted">Note: All assets will be checked for approval, you won't get a refund if your asset was disapproved.</p>
   <div class="container">
        <div class="row">
            <div class="col-sm">
            <div class="alert alert-upload alert-danger" style="display: none;" role="alert">
                This is a secondary alert—check it out!
            </div>
                <div class="m-auto" style="max-width: 35rem">
                    <form method="post" enctype="multipart/form-data" id="create">
                        <div class="form-group"><label>Name</label> <input type="text" id="name" name="name" placeholder="Asset name" value="" required="required" class="form-control"><small class="form-text text-muted">Name this to whatever, slurs and other inappropriate things will be moderated.</small></div>
                        <div class="form-group"><label>Description</label> <textarea id="desc" name="description" placeholder="Asset description" class="form-control"></textarea></div>
                        <div class="form-group"><label>Price</label> <input type="number" max="10000" min="0" step="1" id="price" name="price" placeholder="Asset price" value="10" required="required" class="form-control"><small class="form-text text-muted">You do not need to provide a price for any asset yet the maximum is 10000 rocks.</small></div>
                        <div class="form-group"><label>File</label> <input type="file" id="file" name="file" class="form-control-file"></div>
                        <div class="form-group">
                        <label>Asset Type</label> 
                        <select id="type" name="type" required="required" class="form-control">
                            <option value="2">T-Shirt</option>
                            <option value="11">Shirt</option>
                            <option value="12">Pants</option>
                            <option value="13">Decal</option>
                        </select>
                        <small class="form-text text-muted">Staff can only upload hats, gears, faces, audios and heads.</small>
                        </div>
                        <div class="form-group mb-0">
                        <button class="btn btn-upload btn-success btn-block"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Create</button><small class="text-muted">Creating an asset costs 5 Rocks.</small>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-sm">
                <div class="card bg-light mb-3 shadow-sm" style="max-width: 29rem;">
                    <div class="card-header">Asset Template</div>
                        <div class="card-body">
                            <img src="template.png" height="420" width="420">
                            <small class="text-muted">Make sure to resize the template to <code>585x559</code></small>
                            <br>
                            <small class="text-muted">This template also works with pants (left leg, right leg)</small>
                        </div>
                </div>
                <a href="#" style="display: grid; place-items: center;">> Looking to upload special assets? Click here. <</a>
            </div>
        </div>
    </div>
</div>
<script>

var currentType = "danger";

function showAlert(text, type)
{
    $(".alert-upload").text(text).removeClass("alert-"+currentType).addClass("alert-"+type).show();
    $(".btn-upload").removeAttr("disabled").find("span").hide();
    currentType = type;
}

   $('#file').change(function(event){ $('#name').val(event.target.files[0].name.split('.')[0]).select(); $('#desc').val(event.target.files[0].name.split(".")[0]).select(); });

  $('.btn-upload').on('click', function()
  { 
    var fdata = new FormData();
    fdata.append('file', $('#file')[0].files[0]);
    fdata.append('name', $('#name').val());
    fdata.append('desc', $("#desc").val());
    fdata.append('price', $("#price").val());
    fdata.append('type', $("#type").val());

    $(this).attr("disabled", "disabled").find("span").show();
    
    $.ajax(
    {
        url: '/api/develop/upload',
        type: 'POST',
        data: fdata,
        contentType: false,
        processData: false,
        success: function(response)
        {
            showAlert(response.message, response.success ? "success" : "danger");
        },
        error: function()
        {
            showAlert(response.message, "danger");
        }
    });
  });
</script>
<?php pageBuilder::buildFooter(); ?>