<?php
$myFile = "js/data.js";
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = $_POST["savedData"];


echo $stringData;

fwrite($fh, $stringData);
fclose($fh)
?>