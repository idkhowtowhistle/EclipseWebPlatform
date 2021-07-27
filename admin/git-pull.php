<?php
$output = [];
exec("git pull 2>&1", $output, $exitcode);

foreach($output as $line) echo "$line<br>";
if($exitcode != 0) echo "<br><br> Program exited with code $exitcode";