<?php 
//header("Pragma: no-cache");
//header("Cache-Control: no-cache");
header("content-type: image/png");

$x = $_GET['x'] ?? 100;
$y = $_GET['y'] ?? 100;
$id = $_GET['id'] ?? $_GET['userId'] ?? false;

$sizes = 
[
    "48x48" => true,
    "75x75" => true,
    "100x100" => true,
    "110x110" => true,
    "250x250" => true,
    "352x352" => true,
    "420x420" => true,
];

if($x == 200 && $y == 200) { $x = 250; $y = 250; }
if(!isset($sizes["{$x}x{$y}"])) { $x = 100; $y = 100; }

$filename = "{$x}x{$y}.png";
if(!file_exists("./avatars/$id-$filename")) die(readfile("./avatars/sample-$filename"));
readfile("./avatars/$id-$filename");