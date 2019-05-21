<?php

// includes
include dirname(__DIR__) . '/config.php';

$data = array();

$id = 0;

//////
// array init
if (false) {
	/*$id = -1;
	$data[$id] = (object) array(
		'id' => $id,
		'name' => "ARTIST",
		'genius' => (object) array(
			'id' => "ID_GENIUS",
			'artistName' => "NAME_GENIUS"
		),
		'itunes' => (object) array(
			'id' => "ID_ITUNES",
			'artistName' => "NAME_ITUNES"
		),
		'instagram' => (object) array(
			'id' => "ID_INSTAGRAM",
			'username' => "NAME_INSTAGRAM"
		),
		'twitter' => (object) array(
			'id' => "ID_TWITTER",
			'username' => "NAME_TWITTER"
		),
		'band' => false,
		'updates' => (object) array(
			'auto' => new DateTime(),
			'manually' => false
		)
	);*/
	$id = 0;
	$data[$id] = (object) array(
		'id' => $id,
		'name' => "Passi",
		'genius' => (object) array(
			'id' => "ID_GENIUS",
			'artistName' => "Passi"
		),
		'itunes' => (object) array(
			'id' => "15048020",
			'artistName' => "Passi"
		),
		'instagram' => (object) array(
			'id' => "ID_INSTAGRAM",
			'username' => "NAME_INSTAGRAM"
		),
		'twitter' => (object) array(
			'id' => "ID_TWITTER",
			'username' => "NAME_TWITTER"
		),
		'band' => false,
		'updates' => (object) array(
			'auto' => false,
			'manually' => false
		)
	);
	writeJSONFile("socials", $data);
	exit;
}

//////
// array example
$id++;
$data = json_decode(file_get_contents(DIR_DATA . "socials.json"), true);

$albums = json_decode(file_get_contents(DIR_DATA . $file_prefixe . date("Ymd") . ".json"), true);
foreach ($albums['today'] as $year => $entities) {
	foreach ($entities as $i => $album) {
		print_r($album/*['artist']*/);
	}
}

writeJSONFile("socials", $data);
exit;

//echo json_encode($data);
//var_dump($data);

//////
// get artist
function get_artist_by_id($id, $source = null) {
	global $data;

	if (!is_numeric($id) || intval($id) === 0)
		return false;

	if (!$source)
		return isset($data[$id]) ? $data[$id] : false;

	switch ($source) {
		case 'genius':
		case 'itunes':
		case 'instagram':
		case 'twitter':
			foreach ($data as $artist) {
				//if (intval($artist->$source->id) === intval($id)) {
				if ($artist->$source->id === $id) {
					return $artist;
				}
			}
		default:
			return false;
	}

	return false;
}
function get_artist_by_name($name, $source = null) {
	global $data;
	foreach ($data as $id => $artist) {
		if (strtolower($artist->name) === strtolower($name) || 
			strtolower($artist->genius->artistName) === strtolower($name) || 
			strtolower($artist->itunes->artistName) === strtolower($name)) {
			return $artist;
		}
	}
	return false;
}

//echo json_encode(get_artist_by_id(1));


//////
// array example


//////
// array example