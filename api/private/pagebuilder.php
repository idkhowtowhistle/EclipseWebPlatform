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
		"https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.19.1/css/mdb.min.css",
		"/css/alert-styles.css",
		"/css/addons/datatables.min.css",
		"/css/toastr.css",
		"https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css",
		"/css/mercury.css"
	];

	public static array $pageConfig =
	[
		"title" => SITE_CONFIG["site"]["name"],
		"og:site_name" => SITE_CONFIG["site"]["name"],
		"og:url" => "https://banland.xyz",
		"og:description" => "PlayStation®Network free hacks and mods for call of duty",
		"og:image" => "/img/eclipse-logofull.png",
		"navbar" => true,
		"coolBackground" => false,
		"XD" => SESSION ? SESSION["theme"] == 1 : false,
		"nobelium" => SESSION ? SESSION["theme"] == 2 : false,
		"krypton" => SESSION ? SESSION["theme"] == 3 : false,
		"dark" => SESSION ? SESSION["theme"] == 4 : false
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

		$userAgent = $_SERVER["HTTP_USER_AGENT"] ?? "";
		if (strpos($userAgent, "ROBLOX Android App") !== false) self::$pageConfig["navbar"] = false;

		$path = strtok($_SERVER["REQUEST_URI"] ?? "/", "?");
		$isActive = function ($prefix) use ($path) {
			return strpos($path, $prefix) === 0 ? " active" : "";
		};
		$search = htmlspecialchars($_GET["q"] ?? "", ENT_QUOTES);
		$unread = SESSION ? users::getUnreadMessages(SESSION["userId"]) : 0;
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="polygon-csrf" content="<?= SESSION ? SESSION["csrfToken"] : 'false' ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="theme-color" content="#0f0e11">
		<link rel="icon" type="image/png" href="/img/eclipse.png" />
		<title><?= self::$pageConfig["title"] ?? SITE_CONFIG["site"]["name"] ?> - <?= SITE_CONFIG["site"]["name"] ?></title>
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
	</head>
	<body<?= !empty(self::$pageConfig["bodyClass"]) ? ' class="' . self::$pageConfig["bodyClass"] . '"' : '' ?>>
		<?php /* navbar */ if (self::$pageConfig["navbar"]) { ?>
		<nav class="mnav" id="mercNav">
			<div class="mnav-inner">
				<a class="mnav-brand" href="/">
					<img src="/img/eclipse.png" alt="<?= SITE_CONFIG["site"]["name"] ?>" class="d-lg-none">
					<span class="d-none d-lg-inline"><?= SITE_CONFIG["site"]["name"] ?></span>
				</a>
				<button class="mnav-toggle" type="button" aria-label="Toggle navigation" onclick="document.getElementById('mercNav').classList.toggle('open')">
					<i class="fas fa-bars"></i>
				</button>
				<div class="mnav-links">
					<?php if (SESSION) { ?>
					<a class="nav-item<?= $isActive("/games") ?>" href="/games">Games</a>
					<a class="nav-item<?= $isActive("/catalog") ?>" href="/catalog">Catalog</a>
					<a class="nav-item<?= $isActive("/create") ?>" href="/create/new">Create</a>
					<a class="nav-item<?= $isActive("/forum") ?>" href="/forum">Forum</a>
					<a class="nav-item<?= $isActive("/browse") ?>" href="/browse">Users</a>
					<?php } else { ?>
					<a class="nav-item<?= $isActive("/forum") ?>" href="/forum">Forum</a>
					<a class="nav-item<?= $isActive("/about") ?>" href="/about">About</a>
					<?php } ?>
				</div>
				<form class="mnav-search" action="/browse" method="get" role="search">
					<input type="search" name="q" value="<?= $search ?>" placeholder="Search" aria-label="Search" autocomplete="off">
				</form>
				<div class="mnav-right">
					<?php if (!SESSION) { ?>
					<a class="btn btn-secondary btn-sm" href="/login">Log in</a>
					<a class="btn btn-primary btn-sm" href="/register">Register</a>
					<?php } else { ?>
					<a class="mnav-icon" href="/messages" aria-label="Messages">
						<i class="far fa-bell"></i>
						<?php if ($unread) { ?><span class="mnav-alert"><?= $unread > 9 ? "9+" : $unread ?></span><?php } ?>
					</a>
					<a class="mnav-icon d-none d-md-inline-flex" href="/economy" data-toggle="tooltip" data-html="true" data-placement="bottom" title="Your next rock mine is in <br> <?= general::time_elapsed("@" . SESSION["nextStipend"], true, false) ?>">
						<i class="fas fa-gem"></i> <?= SESSION["currency"] ?>
					</a>
					<div class="dropdown mnav-user">
						<a href="/user/<?= SESSION["userId"] ?>" class="d-flex align-items-center text-decoration-none">
							<img src="/thumbnails/headshot?id=<?= SESSION["userId"] ?>&x=150&y=150" alt="<?= SESSION["userName"] ?>" class="mnav-avatar">
							<span class="mnav-username d-none d-lg-inline"><?= SESSION["userName"] ?></span>
						</a>
						<a class="mnav-icon dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Account menu">
							<i class="fas fa-caret-down"></i>
						</a>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
							<a class="dropdown-item" href="/user/<?= SESSION["userId"] ?>"><i class="far fa-user mr-2"></i>Profile</a>
							<a class="dropdown-item" href="/character"><i class="fas fa-user-pen mr-2"></i>Character</a>
							<a class="dropdown-item" href="/friends"><i class="fas fa-user-group mr-2"></i>Friends</a>
							<a class="dropdown-item" href="/economy"><i class="fas fa-money-bill-transfer mr-2"></i>Economy</a>
							<a class="dropdown-item" href="/settings"><i class="fas fa-gears mr-2"></i>Settings</a>
							<?php if (SESSION["adminLevel"] > 1) { ?>
							<a class="dropdown-item" href="/admin/dashboard"><i class="fas fa-diamond-half-stroke mr-2"></i>Admin</a>
							<?php } ?>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item text-danger" href="/logout"><i class="fas fa-arrow-right-from-bracket mr-2"></i><b>Log out</b></a>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>
		</nav>
		<?php } ?>
		<noscript>
			<div class="alert alert-skinny mb-0 text-center" role="alert" style="background-color: #46161a; color: #f7c6c9">
				<?= SITE_CONFIG["site"]["name"] ?> requires JavaScript to function properly.
			</div>
		</noscript>
		<?php while ($row = $banners->fetch(PDO::FETCH_OBJ)) { ?>
		<div class="alert alert-skinny mb-0 text-center <?= $row->textcolor ?>" role="alert" style="background-color: <?= $row->color ?>">
			<?= $row->message ?>
		</div>
		<?php } ?>
		<div class="app py-4 container"<?php if (SESSION) { echo " data-user-id='" . SESSION["userId"] . "'"; } ?>>

	<?php } static function buildFooter() { ?>
		<?= self::$additionalFooterStuff ?>
		</div>
		<?php /* footer */ if (self::$pageConfig["navbar"]) { ?>
		<?php if (SESSION) { ?>
		<nav class="mnav-bottom">
			<a href="/games"><i class="fas fa-mountain-sun"></i>Games</a>
			<a href="/catalog"><i class="fas fa-book-open-cover"></i>Catalog</a>
			<a href="/create/new"><i class="fas fa-plus"></i>Create</a>
			<a href="/forum"><i class="fas fa-messages"></i>Forum</a>
			<a href="/messages"><i class="fas fa-bell"></i>Messages</a>
		</nav>
		<?php } ?>
		<footer class="mfooter">
			<div class="mfooter-inner">
				<div class="mfooter-brand">
					<img src="/img/eclipse.png" alt="<?= SITE_CONFIG["site"]["name"] ?>">
					<a href="/"><?= SITE_CONFIG["site"]["name"] ?></a>
					<span class="mfooter-tagline">&ndash; Be Anything, Build Anything.</span>
				</div>
				<div class="mfooter-right">
					<div class="mfooter-links">
						<a href="/about">About</a>
						<a href="/status">Statistics</a>
						<a href="/rules">Rules</a>
						<a href="/tos">Terms of Service</a>
						<a href="/policy">Privacy Policy</a>
					</div>
					<div class="mfooter-note">&copy; <?= date("Y") ?> <?= SITE_CONFIG["site"]["name"] ?></div>
				</div>
			</div>
		</footer>
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

			$code = (int)$code;

			if (is_array($param) && count($param)) $text[200] = $param;
			else if (!empty($param) && $code == 500) $text[500]["text"] .= " <br> Error ID: <code>$param</code>";

			if (!isset($text[$code])) $code = 404;

			if (!headers_sent()) http_response_code($code);
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
