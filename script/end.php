<?php
// END : 19h30
// cron : 30 19 * * *	php script_post.php
include dirname(__DIR__) . '/config.php';

$file = dirname(__DIR__) . "/data/" . $file_prefixe . date("Ymd") . ".json";
clearImgs();
if (is_file($file))
	unlink($file);