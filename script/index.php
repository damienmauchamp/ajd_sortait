<?php
include 'start.php';

$file = $file_prefixe . date("Ymd") . ".json";
if (is_file($file)) {
	echo file_get_contents($file);
}