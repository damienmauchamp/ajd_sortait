<?php

include_once dirname(__DIR__) . '/config.php';
header("Content-type:application/json");

use \InstagramAPI\Instagram;
use Abraham\TwitterOAuth\TwitterOAuth;

$json = file_get_contents(DIR_DATA . "socials.json");
$data = json_decode($json, true);

// instagram logging
$debug = false;
$ig = new Instagram($debug);
try { // connexion
	$ig->login($_ENV["INSTAGRAM_USERNAME"], $_ENV["INSTAGRAM_PASSWD"]);
} catch (\Exception $e) {
	echo 'Impossible de se connecter à Instagram: ' . $e->getMessage() . "\n";
	exit(0);
}

// twitter logging
$twit = new TwitterOAuth($_ENV["TWITTER_API_KEY"], $_ENV["TWITTER_API_SECRET_KEY"], $_ENV["TWITTER_ACCESS_TOKEN"], $_ENV["TWITTER_ACCESS_TOKEN_SECRET"]);
$twit->setTimeouts(60, 30);

// loop
foreach ($data as $i => $artist) {

	// instagram
	$data[$i]['instagram'] = instagram_user($artist['instagram']);

	// instagram
	$data[$i]['twitter'] = twitter_user($artist['twitter']);
}

// functions
function instagram_user($data = null) {
	global $ig;

	//
	if ($data === null || $data['username'] === null) {
		return null;
	}

	//
	try {
		$id = $ig->people->getUserIdForName($data['username']);
		echo "\n[INSTAGRAM] ID found for username '".$data['username']."' : $id";
	} catch (\Exception $e) {
		$id = null;
		echo "\n[INSTAGRAM] No ID found for username '".$data['username']."'";
	}

	if ($id === null) {
		// user does not exist
		return null;
	}

	return array(
		'id' => intval($id),
		'username' => $data['username']
	);
}
function twitter_user($data = null) {
	global $twit;

	//
	if ($data === null || $data['username'] === null) {
		return null;
	}

	//
	try {
		$user_data = $twit->get('users/lookup', array('screen_name' => $data['username']));
		echo "\n[Twitter] Results found for term '".$data['username']."'";
	} catch (\Exception $e) {
		$user_data = null;
		echo "\n[Twitter] No results found for term '".$data['username']."'";
	}

	if ($user_data === null) {
		// user does not exist
		return null;
	}

	//
	$id = null;
	foreach ($user_data as $i => $user) {
		//print_r($user);
		if (strtolower($user->screen_name) === strtolower($data['username'])) {
			$id = $user->id;
			echo "\n[Twitter] ID found for username '".$data['username']."' : $id";
			break;
		}
	}

	if ($id === null) {
		echo "\n[Twitter] No ID found for username '".$data['username']."'";
		return null;
	}

	return array(
		'id' => intval($id),
		'username' => $data['username']
	);
}


writeJSONFile("socials", $data);
//exit(json_encode($data));
