<?php
// POST à partir de 9h toutes les 10 minutes
// TODO : today_notFound
// cron : */10 8-19 * * *	php script_post.php
include dirname(__DIR__).'/config.php';

//header("Content-type:text/html");

use \Bot\Album;
use \Bot\Post\InstagramPost;
use \Bot\Post\TwitterPost;

$instagram_off = true;

$res = [];
$album_path = dirname(__DIR__)."/data/".($file_prefixe ?? '').date("Ymd").".json";
if(!is_file($album_path)) {
	echo logsTime()."[POST] No file found for today\n";
	exit;
}
$json = file_get_contents($album_path);
$results = json_decode($json, true);
$res["before"] = $results;

//
$prod = $_ENV['ENVIRONMENT'] === 'prod' || $_ENV['ENVIRONMENT'] === 'production';
$simulated_suffix = $prod ? '' : ' - SIMULATED';
$debug = isset($_ENV['DEBUG']) && boolval($_ENV['DEBUG']);

echo "\n";
if(intval($results["todayCount"]) === 0) {
	echo "===========================================================\n";
	echo "===========================================================\n";
	echo ($debug ? "[DEBUG] " : "").date('Y-m-d H:i:s', strtotime('now'))." [".$_ENV['ENVIRONMENT']."]\n";
	echo "Nothing to post.\n";
	echo "===========================================================\n";
	echo "===========================================================\n";
	exit(false);
}

echo "===========================================================\n";
echo "===========================================================\n";
echo ($debug ? "[DEBUG] " : "").date('Y-m-d H:i:s', strtotime('now'))." [".$_ENV['ENVIRONMENT']."]\n";
echo "[PROCESS] : ".cli_get_process_title()."\n";
echo "===========================================================\n";
echo "===========================================================\n";
echo "\n\n";
foreach($results["today"] as $year => $entities) {
	foreach($entities as $i => $album) {

		// todo: create methods

		// log
		echo logsTime()."'".$album['album']."' by ".$album['artist']." :\n";

		// checking if the album is already posted
		if(isPosted($album)) {
			// that's good
			echo logsTime().'[POST] '."✔️ OK already posted.\n\n\n";
			continue;
		}

		// checking if the posting date is past
		if(!dateExceeded($album)) {
			// we're not posting yet
			echo logsTime().'[POST] '."⌛ post scheduled at ".date('Y-m-d H:i:s', $album["post_date"]).".\n\n\n";
			continue;
		}

		// initializing the entity
		$item = new Album($album);
		if(!is_array($results["today"][$year][$i]["posted"])) {
			$results["today"][$year][$i]["posted"] = [
				'twitter' => false,
				'instagram' => false,
			];
		}
		if(!is_array($results["today"][$year][$i]["errors"])) {
			$results["today"][$year][$i]["errors"] = [
				'twitter' => false,
				'instagram' => false,
			];
		}

		// checking if the album has an artwork
		if(!$item->getArtwork()) {
			// no artwork found
			echo logsTime().'[POST] '."❌ FAILED - no artwork found.\n\n\n";
			continue;
		}

		// checking if we faced an error earlier
		if ($results["today"][$year][$i]["errors"]["twitter"]) {
			echo logsTime().'[POST] '."⭕ SKIPPED - we faced an error earlier.{$simulated_suffix}\n\n\n";
			continue;
		}

		// checking if the album is posted on Twitter
		if(isPostedTwitter($results["today"][$year][$i]["posted"])) {
			echo logsTime().'[POST] '."✔️ OK - already posted on twitter !{$simulated_suffix}\n\n\n";
			continue;
		}

		// we're now trying to post on Twitter
		echo logsTime().'[POST] '."⌛ WAITING - posting on twitter...\n";
		$twitter = new TwitterPost($item);
		$twitterRes = $twitter->post($prod, $debug);

		$posted = $twitterRes['posted'];
		$error = $twitterRes['error'];
		$message = $twitterRes['message'];

		$results["today"][$year][$i]["posted"]["twitter"] = $posted;
		$results["today"][$year][$i]["errors"]["twitter"] = $error;

		$suffixe = $error ? " - {$error} - {$message} " : '';

//		$results["today"][$year][$i]["posted"]["twitter"] = $twitterRes;
		echo logsTime().'[POST] '.($twitterRes ? "✅ POSTED" : "❌ ERROR")."!{$suffixe}{$simulated_suffix}\n\n\n";
		writeJSONFile(PREFIX_ALBUM_FILE.date("Ymd"), $results);
	}
}
echo "===========================================================\n";
echo "===========================================================\n";
echo "\n\n";

writeJSONFile(($file_prefixe ?? '').date("Ymd"), $results);
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
