<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
users::requireLogin();
pageBuilder::buildHeader();
?>

<div class="alert alert-primary" role="alert">
  For information about self-hosting & security, click <a href="/games/help">here</a>.
</div>
<?php if(SESSION["gameban"]) { ?> 
		<div class="alert alert-danger" role="alert">Your account has been game banned. You cannot host/join any server.</div>
	<?php } ?>
<div class="row d-flex justify-content-center">
<div class="col-6">
   <div class="card">
      <div class="card-header">
      <?=SITE_CONFIG["site"]["name"]?> Server Setup
      </div>
      <div class="card-body">
      <div class="form-group mb-3">
         <label for="serverName">Server Name</label>
         <input type="text" class="form-control" id="serverName" placeholder="Choose a name for your server! (3-256 chars)">
      </div>
      <div class="form-group mb-3">
         <label for="ipAddress">IP Address</label>
         <input type="text" class="form-control" id="serverIp" placeholder="IPV4" value="<?=$_SERVER["REMOTE_ADDR"]?>">
      </div>
      <div class="form-group mb-3">
         <label for="serverPort">Server Port</label>
         <input type="text" class="form-control" id="serverPort" placeholder="Usually 53640" value="53640">
      </div>
      <div class="form-group mb-3">
         <label for="limit">Player Limit</label>
         <input type="number" min="1" placeholder="Server Limit" class="form-control" name="limit" id="limit" value="1">  
      </div>
      <hr>
      <div class="form-group mb-3">
         <label for="privacy">Private Server</label><br>
         <input type="checkbox" id="privacy" data-toggle="toggle" data-size="sm">
      </div>
      <div class="form-group">
         <label for="desc">Server Description</label>
         <textarea class="form-control" placeholder="3-512 characters" id="desc" rows="3"></textarea>
      </div>
      <hr>
      <button type="button" data-control="createServer" class="btn btn-block btn-success" <?= SESSION["gameban"] ? "disabled" : ""?>><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none"></span> Create Server</button>
      </div>
   </div>
</div>
<div class="col-4">
   <div class="card">
      <div class="card-header">
      <?=SITE_CONFIG["site"]["name"]?> Server Info
      </div>
      <div class="card-body">
         Make sure to read the <a href="/help/hosting">guide</a> about hosting before you begin.
         <br>
         <ul>
            <li>
             You can report any exploiters in the Discord or you can message an admin.
            </li>
         </ul>
      </div>
   </div>
</div>
</div>
<script>
     $('button[data-control="createServer"]').on('click', this, function()
  {
  	var button = this; 
    $(button).attr("disabled", "disabled").find("span").show();
    $.post('/api/games/createServer', {"sName":$("#serverName").val(), "sPort":$("#serverPort").val(), "sIp":$("#serverIp").val(), "sLimit":$("#limit").val(), "private":$("#privacy").is(":checked"), "sDesc":$("#desc").val()}, function(data)
    {
      if(data.success){ toastr["success"](data.message); }
      else{ toastr["error"](data.message); }
      $(button).removeAttr("disabled").find("span").hide();
    });
  });
</script>
<?php pageBuilder::buildFooter();?>