<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
?>
<!--div id="pluginObjDiv">
	<object id="robloxnpapipluginobj" type="application/x-vnd-roblox-launcher">
	<object id="robloxactivexpluginobj" classid="clsid:76D50904-6780-4C8B-8986-1A7EE0B1716D" codebase="www.roblox.com" type="application/x-vnd-roblox-launcher">
</div>
<script> 
function log(msg)
{
	var test = "a";
	if($(".debug-log").text().length == 0)
		$(".debug-log").text(msg + "\n");
	else
		$(".debug-log").text($(".debug-log").text() + msg + "\n");
}

function objectToString(object)
{
	return JSON.stringify(object, function(key, val) { return (typeof val === 'function') ? '' + val : val; });
}

function testApp()
{
	log("Properties of window.external: " + objectToString(window.external));
	log("Properties of ActiveX plugin object: " + objectToString(document.getElementById('robloxactivexpluginobj')));
	log("Properties of NPAPI plugin object: " + objectToString(document.getElementById('robloxnpapipluginobj')));

	log("")
	log("Trying window.external launch");

	try
	{
		window.external.StartGame("ticket", "http://banland.xyz/", "http://banland.xyz/Game/Visit.ashx");
	}

	catch (e) 
	{
		log("window.external launch failed");
	}
}
</script-->
<h3 class="font-weight-normal">App Debug</h3>
<div class="card shadow-sm">
	<div class="card-body">
		<p class="card-text">HTTP_USER_AGENT: <code><?=$_SERVER["HTTP_USER_AGENT"] ?? "N/A"?></code></p>
		<p class="card-text">HTTP_X_REQUESTED_WITH: <code><?=$_SERVER["HTTP_X_REQUESTED_WITH"] ?? "N/A"?></code></p>
		<button class="btn btn-success" onclick="testApp()">Debug</button>
		<h5 class="text-uppercase text-muted mt-2">Debug Log</h5>
		<pre class="debug-log"></pre>
	</div>
</div>
<?php pageBuilder::buildFooter(); ?>