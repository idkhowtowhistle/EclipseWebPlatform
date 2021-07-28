<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
if(!SESSION || SESSION["adminLevel"] < 2) pageBuilder::errorCode(404);
pageBuilder::buildHeader();
?>
<h3 class="my-4 font-weight-normal"><?=SITE_CONFIG["site"]["name"]?> Site Management</h3>
<div class="row py-4">
	<div class="col-4">
		<div class="card hover-shadow w-100 mt-2">
			<div class="card-body">
				<h4 class="font-weight-normal"><u>Git Pull</u></h4>
				<ul>
					<li>This downloads any new updates on the GitHub repository onto the website. You'll probably be using this very frequently.</li>
					<li>Here's a secret tip: you can do Ctrl + / on any page to do a Git Pull from anywhere!</li>
				</ul>
				<h4 class="font-weight-normal"><u>PHPMyAdmin</u></h4>
				<ul>
					<li>Need to directly interact with the database? This is exactly what you need, just as long as you have the credentials.</li>
				</ul>
			</div>
		</div>
    </div>
    <div class="col-8">
		<div class="row">
			<div class="col-lg-6 mt-2">
				<a href="#" class="text-reset text-decoration-none gitpull">
					<div class="card hover-shadow w-100">
						<div class="card-body text-center">
							<h1 class="display-4 my-4"><i class="fab fa-git-alt text-danger"></i></h1>
							<h3 class="font-weight-normal my-4">Git Pull</h3>
						</div>
					</div>
				</a>
			</div>
			<div class="col-lg-6 mt-2">
				<a href="https://phpmyadmin.pizzaboxer.xyz" class="text-reset text-decoration-none">
					<div class="card hover-shadow w-100">
						<div class="card-body text-center">
							<h1 class="display-4 my-4"><i class="fal fa-database text-warning"></i></h1>
							<h3 class="font-weight-normal my-4">PHPMyAdmin</h3>
						</div>
					</div>
				</a>
			</div>
		</div>
	</div>
</div>
<?php pageBuilder::buildFooter(); ?>