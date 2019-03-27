<?php
// POST à partir de 9h toutes les 10 minutes
// TODO : today_notFound
// cron : */10 8-19 * * *	php script_post.php
include dirname(__DIR__) . '/config.php';

$res = array();
$json = file_get_contents(dirname(__DIR__) . "/data/" . $file_prefixe . date("Ymd") . ".json");
$results = json_decode($json, true);
$res["before"] = $results;

if (intval($results["todayCount"]) === 0) {
	echo false;
	exit;
}

foreach ($results["today"] as $year => $entities) {
	foreach ($entities as $i => $album) {
		if (!isPosted($album) && dateExceeded($album)) {
			
			$twitter = twitterPost($album);
			$instagram = instagramPost($album);
			$results["today"][$year][$i]["posted"] = true;

			if (isset($_GET["debug"]) && intval($_GET["debug"]) === 1) {
				header("Content-type:text/html");
				ini_set("xdebug.var_display_max_children", -1);
				ini_set("xdebug.var_display_max_data", -1);
				ini_set("xdebug.var_display_max_depth", -1);
				var_dump($instagram);
			    break;
			}

			//echo $album["album"] . " " . date("Y-m-d H:i:s", $album["post_date"]) . " < " . date("Y-m-d H:i:s", strtotime("now")) . "\n";
		}
		continue;
	}
}

writeJSONFile($file_prefixe . date("Ymd"), $results);
$res["after"] = $results;
echo json_encode($res);

function isPosted($album) {
	return $album["posted"];
}

function dateExceeded($album) {
	return $album["post_date"] < strtotime("now");
}