<?php

// includes
include dirname(dirname(__DIR__)) . '/config.php';

// checking if the file exists
if (!is_file(DIR_DATA . "/socials.json")) {
	include_once './init.php';
}

// getting data
$json = file_get_contents(DIR_DATA . "socials.json");
$data = json_decode($json, true);

//
function retour($cle, $i, $item) {
	return array(
		'cle' => $cle,
		'id' => $i,
		'item' => $item
	);
}

// tests
$name = 'qwerty';

//
$res = null;
foreach ($data as $id => $artist) {

	// by artist name
	if ($artist['name'] == $name) {
		$res = retour('name', $id, $artist);
		break;
	}

	// by genius name
	else if ($artist['genius'] !== null && $artist['genius']['artistName'] == $name) {
		$res = retour('genius', $id, $artist);
		break;
	}

	// by itunes name
	else if ($artist['itunes'] !== null && $artist['itunes']['artistName'] == $name) {
		$res = retour('itunes', $id, $artist);
		break;
	}
}

//
	exit(json_encode($res));
if ($res !== null) {
}

// band etc

exit($json);