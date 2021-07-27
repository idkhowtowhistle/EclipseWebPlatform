<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/api/private/core.php";
?>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
</head>
<style>
   .coolhr 
   {
     margin: 0;
     margin-bottom: 10px;
     border: none;
     width: 400px;
     height: 1px;
     opacity: 0.5;
     background: linear-gradient( to right, red 20%, yellow 20%, yellow 36%, green 36%, green 60%, blue 60%, blue 100% );
   }
</style>
<div class="app container py-4">
  <img width="100" height="100" src="https://cdn.shopify.com/s/files/1/0258/6899/2592/products/DRAGONBALLZSHENLONGANDKIDSGOKU_cff940fb-ca93-42dd-af31-d37d34a10e28.jpg?v=1586915490"><h2 class="font-weight-normal mt-2"><?=SITE_CONFIG["site"]["name"]?> Video</h2>
  <p>Hey, you just recorded a video in <?=SITE_CONFIG["site"]["name"]?>! You could:</p>
  <ul>
    <li>Go to <a href="#" onclick="window.external.OpenVideoFolder();">My Videos</a> folder to check it out!</li>
  </ul>
  <hr class="coolhr">
  <a href="#" onclick="window.external.UploadVideo('', false, 0, ''); window.close();">Not interested, don't bother me again</a>
</div>