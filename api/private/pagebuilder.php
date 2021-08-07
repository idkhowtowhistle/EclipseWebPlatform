<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";

class pageBuilder
{

	public static array $JSdependencies =
	[
		"/js/jquery.min.js",
		"https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js",
		"/js/toastr.js",
		"/js/addons/datatables.min.js",
		"https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js",
		"https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js",
		"/js/custom-protocol-check.min.js"
	];
	public static array $CSSdependencies =
	[
		"/css/site.css",
		"/css/extra.css",
		"/css/fontawesome-pro-v5.15.2/css/all.css",
		"https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css",
		"/css/alert-styles.css",
		"/css/addons/datatables.min.css",
		"/css/toastr.css",
		"https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css"
	];

	public static array $pageConfig =
	[
		"title" => SITE_CONFIG["site"]["name"],
		"og:site_name" => SITE_CONFIG["site"]["name"],
		"og:url" => "https://banland.xyz",
		"og:description" => "PlayStation®Network free hacks and mods for call of duty",
		"og:image" => "/favicon.ico",
		"navbar" => true,
		"coolBackground" => false,
		"XD" => SESSION ? SESSION["theme"] == 1 : false,
		"nobelium" => SESSION ? SESSION["theme"] == 2 : false
	];

	public static string $additionalFooterStuff = "";

	static function showStaticNotification($type, $text)
	{
		self::$additionalFooterStuff .= '<script type="text/javascript">$(function(){ toastr["' . $type . '"]("' . $text . '"); });</script>';
	}
	static function showStaticModal($title, $body, $buttons = "[{}]", $options = "{'show':true}")
	{
		//showModal($(this).attr("data-title"), $(this).attr("data-text"), [{'class':'btn btn-outline-secondary', 'isDismissButton':true, 'text':'Close'}]);
		$body = trim(preg_replace('/\s\s+/', ' ', $body));
		$body = str_replace('"', '\"', $body);
		self::$additionalFooterStuff .= '<script type="text/javascript">$(function(){ showModal("' . $title . '", "' . $body . '", ' . $buttons . ', ' . $options . '); });</script>';
	}

	static function buildHeader()
	{
		global $pdo;
		$banners = $pdo->query("SELECT * FROM banners WHERE active ORDER BY id DESC");

		if(strpos($_SERVER["HTTP_USER_AGENT"], "ROBLOX Android App") !== false)
			self::$pageConfig["navbar"] = false;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="polygon-csrf" content="<?= SESSION ? SESSION["csrfToken"] : 'false' ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="theme-color" content="#ED3C12">
		<link rel="icon" type="image/png" href="https://banland.xyz/img/mercury.png" />
		<title><?= self::$pageConfig["title"] ?? "Mercury" ?> - <?= SITE_CONFIG["site"]["name"] ?></title>
		<meta property="og:title" content="<?= self::$pageConfig["title"] ?>">
		<meta property="og:site_name" content="<?= self::$pageConfig["og:site_name"] ?>">
		<meta property="og:url" content="<?= self::$pageConfig["og:url"] ?>">
		<meta property="og:description" content="<?= self::$pageConfig["og:description"] ?>">
		<meta property="og:type" content="Website">
		<meta property="og:image" content="<?= self::$pageConfig["og:image"] ?>">
		<?php foreach (self::$CSSdependencies as $url) { ?>
		<link rel="stylesheet" href="<?= $url . "?t=" . time(); ?>">
		<?php } foreach (self::$JSdependencies as $url) { ?>
		<script src="<?= $url ?>"></script>
		<?php } ?>
		<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8692690750276881"
     crossorigin="anonymous"></script>
		<style>
		/* body
		{ 
			display: flex;
			min-height: 100vh;
			flex-direction: column; 
		}

		.app { flex: 1; }

		@media screen and (-ms-high-contrast:active), screen and (-ms-high-contrast:none) 
		{
			.app { flex: none!important; }
		} */

		.navbar-orange {
			border-bottom: none !important;
		}

		.navbar-orange,
		.navbar-orange .nav-item .dropdown-menu {
			background-color: #eb4034 !important;
		}

		/* change the brand and text color */
		.navbar-orange .navbar-brand,
		.navbar-orange .navbar-text,
		.navbar-orange .navbar-nav .nav-link,
		.navbar-orange .nav-item .dropdown-menu .dropdown-item {
			color: rgba(255, 255, 255, 1) !important;
		}

		/* change the color of active or hovered links */
		.navbar-orange .nav-link:hover,
		.darken {
			color: rgba(255, 255, 255, 0.75) !important;
		}

		.navbar-orange .nav-item .dropdown-menu .dropdown-item:hover {
			background-color: transparent;
			color: rgba(255, 255, 255, 0.75) !important;
		}

		.navbar-orange .nav-item .dropdown-menu {
			margin-top: 0px;
			min-width: 0;
			border: 1px solid #343a40;
			border-top: none;
			border-top-left-radius: 0px;
			border-top-right-radius: 0px;
		}

		<?php if (self::$pageConfig["nobelium"]) { ?>
		.navbar.navbar-expand-lg.navbar-dark.bg-dark {
			background: linear-gradient(90deg, #265a7f, #545489) !important;
			border-bottom: none !important;
			color: white !important;		
		}

		.shadow-sm.navbar.nav-secondary.navbar-expand-lg.navbar-dark.py-0 {
			background: linear-gradient(90deg, #1c425d, #404065);
			border-bottom: none !important;
			color: white !important;
		}

		.navbar-secondary .nav-link {
			color: white !important;
		}

		<?php } ?>
		.navbar-orange-secondary .nav-item {
			margin-left: 0px !important;
			margin-right: 0px !important;
		}

		.navbar-orange-secondary .nav-link {
			color: white !important;
			padding-top: .25rem !important;
			padding-bottom: .25rem !important;
		}

		.navbar-toggler {
			border-color: transparent !important;
		}

		.divider-top {
			border-top: 1px solid #ccc;
		}

		.divider-bottom {
			border-bottom: 1px solid #ccc;
		}

		.shadow, .hover:hover {
			box-shadow: 0 10px 20px rgba(0, 0, 0, .08), 0 4px 8px rgba(0, 0, 0, .06);
		}

		.disabled-wrapper {
			display: inline-block;
		}

		.disabled-wrapper .btn[disabled] {
			pointer-events: none;
		}

		.alert p {
			margin-bottom: 0;
		}

		.nav-tabs.flex-column {
			border-bottom: none;
		}

		.nav-tabs.flex-column .active {
			border-color: #dee2e6 #ffffff #dee2e6 #dee2e6 !important;
			border-bottom-left-radius: .25rem;
			border-top-right-radius: 0;
		}
		</style>
	</head>
	<body>
		<?php /* navbar */ if (self::$pageConfig["navbar"]) { ?>
		<?php /* polygon navbar */ if (self::$pageConfig["XD"]) { ?>
		<nav class="navbar navbar-expand-lg navbar-dark navbar-orange py-0">
		<?php /* polygon navbar */ } else { ?>
		<nav class="navbar navbar-expand-lg navbar-<?=self::$pageConfig['nobelium']?"dark":"light"?> bg-<?=self::$pageConfig['nobelium']?"dark":"light"?>">
		<?php /* polygon navbar */ } ?>
			<div class="container">
				<a class="navbar-brand" href="/"><?= SITE_CONFIG["site"]["name"] ?></a>
				<button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="navbar-collapse collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item">
							<a class="nav-link" href="/">Home</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/forum">Forum</a>
						</li>
						<?php if (SESSION) { ?>
						<li class="nav-item">
							<a class="nav-link" href="/games">Games</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/catalog">Catalog</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/browse">Users</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="moreDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">More</a>
							<div class="dropdown-menu" aria-labelledby="moreDropdown">
								<a class="dropdown-item" href="/games/new">New Server</a><a class="dropdown-item" href="/status">Site Status</a>
							</div>
						</li>
						<?php } ?>
					</ul>
					<ul class="navbar-nav me-auto">
						<?php /* session */ if (!SESSION) { ?>
						<li class="nav-item">
							<a class="nav-link" href="/login">Login</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/register">Register</a>
						</li>
						<?php /* session */ } else { ?>
						<li class="nav-item">
							<a class="nav-link" href="/messages"><i class="fas fa-envelope"></i>
							<?php if (users::getUnreadMessages(SESSION["userId"])) { ?>
								<span class="badge badge-danger"><?= users::getUnreadMessages(SESSION["userId"]) ?></span>
							<?php } ?>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="/economy" data-toggle="tooltip" data-html="true" data-placement="bottom" title="Your next rock mine is in <br> <?= general::time_elapsed("@" . SESSION["nextStipend"], true, false) ?>"><i class="far fa-money-bill-alt"></i> <?= SESSION["currency"] ?></a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="gamertag" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="far fa-user mr-1"></i> <?= SESSION["userName"] ?> </a>
							<div class="dropdown-menu" aria-labelledby="gamertag">
								<a href="/user?id=<?= SESSION['userId'] ?>" class="dropdown-item">Profile</a><a class="dropdown-item" href="/settings">Settings</a><a class="dropdown-item" href="/my/character">Character</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="/logout">Logout</a>
							</div>
						</li>
						<?php /* session */ } ?>
					</ul>
				</div>
			</div>
		</nav>
		<?php /* session */ if (SESSION) { ?>
		<?php /* polygon nav */ if (self::$pageConfig["XD"]) { ?>
		<nav class="navbar navbar-orange-secondary navbar-expand-lg navbar-dark navbar-top bg-dark py-0" style="background-color: #343a40 !important;">
		<?php /* polygon nav */ } else { ?>
		<nav class="shadow-sm navbar nav-secondary navbar-expand-lg navbar-<?=self::$pageConfig['nobelium']?"dark":"light"?> py-0">
		<?php /* polygon nav */ } ?>
			<div class="container">
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#secondaryNavbar" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="secondaryNavbar">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item mr-1">
							<a class="nav-link" href="/user?id=<?= SESSION['userId'] ?>">Profile</a>
						</li>
						<li class="nav-item mx-1">
							<a class="nav-link" href="/my/character">Character</a>
						</li>
						<li class="nav-item mx-1">
							<a class="nav-link" href="/friends">Friends</a>
						</li>
						<li class="nav-item mx-1">
							<a class="nav-link" href="/create/new">Create</a>
						</li>
						<li class="nav-item mx-1">
							<a class="nav-link" href="/invite">Invite</a>
						</li>
						<?php if (SESSION["adminLevel"] > 1 && SESSION["2fa"]) { ?>
						<li class="nav-item mx-1">
							<a class="nav-link" href="/admin/dashboard">Admin</a>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</nav>
		<?php /* navbar */ } ?>
		<noscript>
			<div class="alert alert-skinny mb-0 text-center" role="alert" style="background-color: red">
				<?= SITE_CONFIG["site"]["name"] ?> requires JavaScript to function properly.
			</div>
		</noscript>
		<?php /* session */ } while ($row = $banners->fetch(PDO::FETCH_OBJ)) { ?>
		<div class="alert alert-skinny mb-0 text-center <?= $row->textcolor ?>" role="alert" style="background-color: <?= $row->color ?>">
			<?= $row->message ?>
		</div>
		<?php } ?>
		<div class="app py-4 container"<?php if (SESSION) { echo " data-user-id='" . SESSION["userId"] . "'"; } ?>>
		<?php } static function buildFooter() { ?>
		<?= self::$additionalFooterStuff ?>
		</div>
		<?php /* footer */ if (self::$pageConfig["navbar"]) { ?>
		<div class="footer">
			<div class="container shadow-sm">
				<p class="my-0"><b>Copyright &copy; <?= SITE_CONFIG["site"]["name"] ?> 2021</b>
				<div class="d-flex align-items-center mt-1 mb-0">
					<p class="my-0"><a href="/about">About</a> | <a href="/rules">Rules</a> | <a href="/tos">Terms Of Service</a> | <a href="/policy">Privacy Policy</a></p>
				</div>
			</div>
		</div>
		<?php /* footer */ } ?>
		<div class="global modal fade" id="primaryModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="primaryModal" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="primaryModalTitle">Modal title</h5>
					</div>
					<div class="modal-body" style="white-space: pre-line">
						undefined
					</div>
					<div class="modal-footer">
						<div class="mx-auto">
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>
			$.ajaxSetup({
				headers: {
					'x-polygon-csrf': $('meta[name="polygon-csrf"]').attr('content')
				}
			});

			toastr.options = {
				"closeButton": true,
				"debug": false,
				"newestOnTop": true,
				"progressBar": true,
				"positionClass": "toast-bottom-right",
				"preventDuplicates": true,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "5000",
				"extendedTimeOut": "1000",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut"
			}

			$(function() {
				$("[data-toggle='tooltip']").tooltip({
					'template': '<div class="tooltip m-1" role="tooltip"><div class="arrow" style="left: 86px;"></div><div class="tooltip-inner"></div></div>'
				});
			});
		</script>
		<script>
			function showModal(title, body, buttons, options) {
				if (options == undefined) options = "show";
				var modalFooter = $("#primaryModal").find(".modal-footer").find(".mx-auto");
				$("#primaryModal").find(".modal-title").html(title);
				$("#primaryModal").find(".modal-body").html(body);
				$("#primaryModal").modal(options);
				
				modalFooter.empty();

				$.each(buttons, function(i, button) {
					var buttonHtml = '<button type="button" class="' + button.class + ' text-center mx-1"';
					if (button.isDismissButton) {
						buttonHtml += ' data-dismiss="modal"';
					}
					if (button.onclick) {
						buttonHtml += ' onclick="' + button.onclick + '"';
					}
					if (button.disabled) {
						buttonHtml += ' disabled="disabled"';
					}
					buttonHtml += '>' + button.text + '</button>';


					modalFooter.html(modalFooter.html() + buttonHtml);
				});
			}
		</script>
		<?php if(SESSION && SESSION["adminLevel"] >= 2) { ?>
		<script>
			function gitpull()
			{
				showModal(
					"<i class=\"fab fa-git-alt text-danger\"></i> Git Pull", 
					"<span class=\"spinner-border spinner-border-sm text-danger\" role=\"status\" aria-hidden=\"true\"></span> Executing Git Pull...", 
					[
						{'class':'btn btn-outline-primary', 'isDismissButton':true, 'text':'Close'},
						{'class':'btn btn-outline-danger disabled', 'disabled':true, 'text':'Run Again'}
					]
				);

				$.get("/admin/git-pull", function(data)
				{
					showModal(
						"<i class=\"fab fa-git-alt text-danger\"></i> Git Pull", 
						"<pre class=\"mb-0\">"+data+"</pre>", 
						[
							{'class':'btn btn-outline-primary', 'isDismissButton':true, 'text':'Close'},
							{'class':'btn btn-outline-success gitpull', 'text':'Run Again'}
						]
					);
				});
			}

			$("body").on("click", ".gitpull", gitpull);

			$("body").keydown(function(event) 
			{
				if (event.originalEvent.ctrlKey && event.originalEvent.key == "/") gitpull();
			});
		</script>
		<?php } ?>
	</body>
</html>
	<?php }

		static function errorCode($code, $param = false)
		{
			$text =
			[
				400 => ["title" => "Bad request", "text" => "There was a problem with your request"],
				404 => ["title" => "Requested page not found", "text" => "You may have clicked an expired link or mistyped the address"],
				420 => ["title" => "Website is currently under maintenance", "text" => "check back later"],
				500 => ["title" => "Unexpected error with your request", "text" => "Please try again after a few moments"]
			];

			if (is_array($param) && count($param)) $text[200] = $param;
			else if (!empty($param) && $code == 500) $text[500]["text"] .= " <br> Error ID: <code>$param</code>";

			http_response_code($code);
			self::buildHeader();
	?>
		<div class="card mx-auto" style="max-width:640px;">
			<div class="card-body text-center">
				<img src="/img/error.png">
				<h2 class="font-weight-normal"><?= $text[$code]["title"] ?></h2>
				<?= $text[$code]["text"] ?>
				<hr>
				<a class="btn btn-outline-primary mx-1 mt-1 py-1" onclick="window.history.back()">Go to Previous Page</a>
				<a class="btn btn-outline-primary mx-1 mt-1 py-1" href="/">Return Home</a>
			</div>
		</div>
<?php
			self::buildFooter();
			die();
		}
	}
