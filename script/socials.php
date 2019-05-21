<?php
//
//
//
include dirname(__DIR__) . '/config.php';

$default = array(
	'id' => null,
	'name' => "ARTIST",
	'genius' => array(
		'id' => "ID_GENIUS",
		'artistName' => "NAME_GENIUS"
	),
	'itunes' => array(
		'id' => "ID_ITUNES",
		'artistName' => "NAME_ITUNES"
	),
	'instagram' => array(
		'id' => "ID_INSTAGRAM",
		'username' => "NAME_INSTAGRAM"
	),
	'twitter' => array(
		'id' => "ID_TWITTER",
		'username' => "NAME_TWITTER"
	),
	'band' => false,
	'updates' => array(
		'auto' => new DateTime(),
		'manually' => false
	)
);

// today's artists
$todays_file = DIR_DATA . PREFIX_ALBUM_FILE . date("Ymd") . ".json";
$today = is_file($todays_file) ? json_decode(file_get_contents($todays_file), true) : false;

// data
$data = is_file(DIR_DATA . "socials.json") ? json_decode(file_get_contents(DIR_DATA . "socials.json"), true) : array();

$newData = $data;

//echo json_encode(find_artist($data, 'Passi'));

// artists names
foreach ($today['today'] as $year => $items) {
	foreach ($items as $release) {
		$artist = find_artist($data, $release['artist']) ?: false;
		if (!$artist) {
			$artist = $default;
			$artist['name'] = $release['artist'];
			$newData[] = $artist;
		}
		//print_r($artist);
	}
}

echo json_encode($newData);
// writeJSONFile("socials", $data);
//echo json_encode($today);
function equals($a, $b) {
	return $a === $b;
}

function find_artist($data, $search) {
	foreach ($data as $id => $artist) {
		// name
		if (equals($artist['name'], $search)) {
			return array('artist' => $artist, 'with' => 'name');
		} else if (equals($artist['genius']['artistName'], $search)) {
			return array('artist' => $artist, 'with' => 'genius');
		} else if (equals($artist['itunes']['artistName'], $search)) {
			return array('artist' => $artist, 'with' => 'itunes');
		} else {
			continue;
		}
	}
	return false;
}
exit;