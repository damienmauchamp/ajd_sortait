<?php
// END : 19h30
// cron : 30 19 * * *	php script_post.php
include 'start.php';

$file = dirname(__FILE__) . "/../data/" . $file_prefixe . date("Ymd") . ".json";
clearImgs();
if (is_file($file))
	unlink($file);