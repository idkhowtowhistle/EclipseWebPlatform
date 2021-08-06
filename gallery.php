<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
pageBuilder::buildHeader();
?>
<div class="jumbotron">
    <div class="row">
        <div class="col">
            <h1 class="font-weight-normal"><?= SITE_CONFIG["site"]["name"] ?> Gallery</h1>
            <h5 class="font-weight-normal">Check out what our users have been posting and catch up with our media!</h5>
        </div>
    </div>
</div>
<h5 class="font-weight-normal">Youtube</h5>
<div class="row">
    <div class="col">
        <div class="card hover-shadow">
            <div class="card-body">
                <iframe class="embed-responsive embed-responsive-16by9" src="https://www.youtube.com/embed/gzsQDePjhvU" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <br>
                <small class="text-muted">Official Mercury Trailer</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card hover-shadow">
            <div class="card-body">
                <iframe class="embed-responsive embed-responsive-16by9" src="https://www.youtube.com/embed/Fq7saMpo7Io" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <br>
                <small class="text-muted">"Mercury : Old Roblox Revival !"</small>
            </div>
        </div>
    </div>
    <div class="w-100"><hr></div>
    <div class="col">
        <div class="card hover-shadow">
            <div class="card-body">
                <iframe class="embed-responsive embed-responsive-16by9" src="https://www.youtube.com/embed/eerIc7RawBs" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <br>
                <small class="text-muted">"Mercury review, +KJF and pizzaboxer play this!!!"</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card hover-shadow">
            <div class="card-body">
                <iframe class="embed-responsive embed-responsive-16by9" src="https://www.youtube-nocookie.com/embed/3PVleglv3lk" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <br>
                <small class="text-muted">"Mercury: A Revival Review"</small>
            </div>
        </div>
    </div>
</div>
<?php pageBuilder::buildFooter(); ?>