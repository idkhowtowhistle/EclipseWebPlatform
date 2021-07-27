<?php
$output = [];
exec("git pull", $output, $exitcode);
var_dump($output);
echo "<br>";
var_dump($exitcode);