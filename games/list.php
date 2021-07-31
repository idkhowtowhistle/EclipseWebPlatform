<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
?>
<div id="pluginObjDiv">
	<object id="robloxnpapipluginobj" type="application/x-vnd-roblox-launcher">
	<object id="robloxactivexpluginobj" classid="clsid:76D50904-6780-4C8B-8986-1A7EE0B1716D" codebase="www.roblox.com" type="application/x-vnd-roblox-launcher">
</div>
<h3 class="font-weight-normal">App Debug</h3>
<div class="card shadow-sm">
	<div class="card-body">
		<p class="card-text">HTTP_USER_AGENT: <code><?=$_SERVER["HTTP_USER_AGENT"] ?? "N/A"?></code></p>
		<p class="card-text">HTTP_X_REQUESTED_WITH: <code><?=$_SERVER["HTTP_X_REQUESTED_WITH"] ?? "N/A"?></code></p>
		<a class="btn btn-success" href="http://banland.xyz/games/start?placeid=100">Join Mercury Bowling</a>
		<h5 class="text-uppercase text-muted mt-2">Debug Log</h5>
		<pre class="debug-log"></pre>
	</div>
</div>
<?php pageBuilder::buildFooter(); ?>