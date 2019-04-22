<?php

// includes
include dirname(__DIR__) . '/config.php';

$data = array();

$id = 0;

//////
// array example
$id++;
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
	'band' => array(5, 65, 98)|false,
	'update' => (object) array(
		'manually' => true|false,
		'last_update' => new DateTime()
	)
);

//echo json_encode($data);
//var_dump($data);

//////
// get artist
function get_artist_by_id($id, $source = null) {
	global $data;

	if (!$source)
		return isset($data[$id]) ? $data[$id] : false;

	if (!is_numeric($id))
		return false;

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

echo json_encode(get_artist_by_id(1));


//////
// array example


//////
// array example