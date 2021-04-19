<?php
// POST à partir de 9h toutes les 10 minutes
// TODO : today_notFound
// cron : */10 8-19 * * *	php script_post.php
include dirname(__DIR__) . '/config.php';
//header("Content-type:text/html");

use \Bot\Album;
use \Bot\Post\TwitterPost;
use \Bot\Post\InstagramPost;

$instagram_off = true;

$res = array();
$json = file_get_contents(dirname(__DIR__) . "/data/" . $file_prefixe . date("Ymd") . ".json");
$results = json_decode($json, true);
$res["before"] = $results;

//
$prod = $_ENV['ENVIRONMENT'] === 'prod' || $_ENV['ENVIRONMENT'] === 'production';
$debug = isset($_ENV['DEBUG']) && boolval($_ENV['DEBUG']);

echo "\n";
if (intval($results["todayCount"]) === 0) {
	echo "===========================================================\n";
	echo "===========================================================\n";
	echo ($debug ? "[DEBUG] " : "") . date('Y-m-d H:i:s', strtotime('now')) . " [" . $_ENV['ENVIRONMENT'] . "]\n";
	echo "Nothing to post.\n";
	echo "===========================================================\n";
	echo "===========================================================\n";
	exit(false);
}

echo "===========================================================\n";
echo "===========================================================\n";
echo ($debug ? "[DEBUG] " : "") . date('Y-m-d H:i:s', strtotime('now')) . " [" . $_ENV['ENVIRONMENT'] . "]\n";
echo "===========================================================\n";
echo "===========================================================\n";
foreach ($results["today"] as $year => $entities) {
	foreach ($entities as $i => $album) {

		echo logsTime() . "'" . $album['album'] . "' by " . $album['artist'] . " :\n";

		if (!isPosted($album) && dateExceeded($album)) { // todo: create methods

			$item = new Album($album);
			if (!is_array($results["today"][$year][$i]["posted"])) {
				$results["today"][$year][$i]["posted"] = array(
					'twitter' => false,
					'instagram' => false
				);
			}

			if (!isPostedTwitter($results["today"][$year][$i]["posted"])) {
			//if (!isPostedTwitter($album)) {
				echo logsTime() . "posting on twitter...\n";
				$twitter = new TwitterPost($item);
				$twitterRes = $twitter->post($prod, $debug);	
				$results["today"][$year][$i]["posted"]["twitter"] = $twitterRes; //true;
				echo logsTime() . ($twitterRes ? "POSTED" : "ERROR") . "!\n";
				writeJSONFile(PREFIX_ALBUM_FILE . date("Ymd"), $results);
			} else {
				echo logsTime() . "\talready posted on twitter !\n";
			}

			if (!$instagram_off) {
				if (!isPostedInstagram($results["today"][$year][$i]["posted"])) {
				//if (!isPostedInstagram($album)) {
					echo logsTime() . "posting on instagram...\n";
					$instagram = new InstagramPost($item);
					$instagramRes = $instagram->post($prod, $debug);
					$results["today"][$year][$i]["posted"]["instagram"] = $instagramRes; //true;
					echo logsTime() . ($instagramRes ? "POSTED" : "ERROR") . "!\n";
					writeJSONFile(PREFIX_ALBUM_FILE . date("Ymd"), $results);
				} else {
					echo logsTime() . "\talready posted on instagram !\n";
				}
			}

			//echo $album["album"] . " " . date("Y-m-d H:i:s", $album["post_date"]) . " < " . date("Y-m-d H:i:s", strtotime("now")) . "\n";

			//
		} else if (!dateExceeded($album)) {
			echo logsTime() . "\tpost scheduled at " . date('Y-m-d H:i:s', $album["post_date"]) . ".\n";
		} else {
			echo logsTime() . "\talready posted.\n";
		}
		echo "\n";
		continue;
	}
}
echo "===========================================================\n";
echo "===========================================================\n";
echo "\n\n";

writeJSONFile($file_prefixe . date("Ymd"), $results);
$res["after"] = $results;
//echo json_encode($res);

function isPosted($album) {
	return $album["posted"] && isPostedTwitter($album) && isPostedInstagram($album);
}

function isPostedTwitter($posted) {
	return isset($posted['twitter']) && $posted['twitter'];
}

function isPostedInstagram($posted) {
	return isset($posted['instagram']) && $posted['instagram'];
}

function dateExceeded($album) {
	return $album["post_date"] < strtotime("now");
}