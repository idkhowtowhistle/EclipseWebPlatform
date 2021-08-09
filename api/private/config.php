<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


define("SITE_CONFIG", 
	[
		"database" =>
		[
			"host" => "localhost",
			"schema" => "mercury",
			"username" => "multako",
			"password" => "pS8r#h&dx4?Xig9k"
		],

		"site" =>
		[
			"name" => "Mercury",
			"name_secondary" => "banlands",
            // dont remove this \/
            "pwsalt" => "947102f9f0e7d3782e48c6591311efe29cdfb274c7876e703e303816c5683a15" 
		],

		"keys" =>
		[
			"DiscordBot" => "d199a6692b5c36f0648380a471fe0a78d9beac440b61fdf532705a65d2d5551fe2302b417a8e7796f1b9fc900f6c8b14a00981b2c4dd9a0a4ce05d01946822d8",
			"renderserver" => "[INSERT KEY HERE]"
		],

		"captcha" =>
		[
			"siteKey" => "6Lfe4GEbAAAAANvrCSH6Q3yc9zlENNDSZQo-_FF5",
			"privateKey" => "6Lfe4GEbAAAAAOQlyBd1Ilbk7FE5dfolX75BD_bn"
		]
	]);