<?php
// POST à partir de 9h toutes les 10 minutes
// TODO : today_notFound
// cron : */10 8-19 * * *	php script_post.php
include dirname(__DIR__) . '/config.php';
//header("Content-type:text/html");

use \Bot\Album;
use \Bot\Post\TwitterPost;
use \Bot\Post\InstagramPost;

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

		echo "'" . $album['album'] . "' by " . $album['artist'] . " :\n";

		if (!isPosted($album) && dateExceeded($album)) { // todo: create methods

			$item = new Album($album);
			if (!is_array($results["today"][$year][$i]["posted"])) {
				$results["today"][$year][$i]["posted"] = array(
					'twitter' => false,
					'instagram' => false
				);
			}

			if (!isPostedTwitter($album)) {
				echo "posting on twitter...\n";
				$twitter = new TwitterPost($item);
				$twitterRes = $twitter->post();
				$results["today"][$year][$i]["posted"]["twitter"] = true;
				echo "POSTED!\n";
			} else {
				echo "--> already posted on twitter !\n";
			}
			if (!isPostedInstagram($album)) {
				echo "posting on instagram...\n";
				$instagram = new InstagramPost($item);
				$instagramRes = $instagram->post();
				$results["today"][$year][$i]["posted"]["instagram"] = true;
				echo "POSTED!\n";
			} else {
				echo "--> already posted on instagram !\n";
			}
			//x->setPosted();
			//si echec :
			//	ajouter à "reste" ?
			// 	poster le reste à la fin de la journée
			//$results["today"][$year][$i]["posted"] = true;

			//echo $album["album"] . " " . date("Y-m-d H:i:s", $album["post_date"]) . " < " . date("Y-m-d H:i:s", strtotime("now")) . "\n";
		} else {
			echo "--> already posted.\n";
		}
		continue;
	}
}

writeJSONFile($file_prefixe . date("Ymd"), $results);
$res["after"] = $results;
//echo json_encode($res);

function isPosted($album) {
	return $album["posted"] && isPostedTwitter($album) && isPostedInstagram($album);
}

function isPostedTwitter($album) {
	if (!$album["posted"] || !is_array($album)) {
		return false;
	}
	return isset($album["posted"]['twitter']) && $album["posted"]['twitter'];
}

function isPostedInstagram($album) {
	if (!$album["posted"] || !is_array($album)) {
		return false;
	}
	return isset($album["posted"]['instagram']) && $album["posted"]['instagram'];
}

function dateExceeded($album) {
	return $album["post_date"] < strtotime("now");
}