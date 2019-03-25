<?php
// POST à partir de 9h toutes les 10 minutes
// TODO : today_notFound
// cron : */10 8-19 * * *	php script_post.php
include 'start.php';

$res = array();

$json = file_get_contents("albums_" . date("Ymd") . ".json");
//echo $json;

$results = json_decode($json, true);
$res["before"] = $results;

if (intval($results["todayCount"]) === 0) {
	echo false;
	exit;
}

$go = true;
foreach ($results["today"] as $year => $entities) {
	foreach ($entities as $i => $album) {
		if (!isPosted($album) && dateExceeded($album)) {
			$results["today"][$year][$i]["posted"] = true;
			$allow->twitter ? twitterPost($album) : null;
			$allow->instagram ? instagramPost($album) : null;
			//echo $album["album"] . " " . date("Y-m-d H:i:s", $album["post_date"]) . " < " . date("Y-m-d H:i:s", strtotime("now")) . "\n";
		}
		continue;
	}
}

writeJSONFile($file_prefixe . date("Ymd"), json_encode($results));
$res["after"] = $results;
echo json_encode($res);

function isPosted($album) {
	return $album["posted"];
}

function dateExceeded($album) {
	return $album["post_date"] < strtotime("now");
}