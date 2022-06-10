<?php
// END : 19h30
// cron : 30 19 * * *	php script_post.php
include dirname(__DIR__) . '/config.php';

// logs
if (!empty($_ENV['LOG_DIR']) && !empty($_ENV['LOG_POST'])) {
	if (is_file($_ENV['LOG_DIR'] . $_ENV['LOG_POST'] . '.log')) {
		rename($_ENV['LOG_DIR'] . $_ENV['LOG_POST'] . '.log', $_ENV['LOG_DIR'] . $_ENV['LOG_POST'] . '_'.date('Ymd') . '.log');
	}
}

$file = dirname(__DIR__) . "/data/" . ($file_prefixe ?? '') . date("Ymd") . ".json";
clearImgs();
if (is_file($file)) {
	unlink($file);
}

// socials
include_once __DIR__ . '/socials.php';