<?php

include dirname(__DIR__) . '/config.php';

$items = array(
	"one_result" => array(
		"album" => "Compilation \"Menace sur la planète rap\" vol.3",
		"artist" => "Artistes multiples",
		"year" => 2007,
		"month" => "03",
		"day" => "27"
	),
	"one_result2" => array(
		"album" => "Extra-Lucide",
		"artist" => "Disiz",
		"year" => 2012,
		"month" => "10",
		"day" => "29"
	),
	"multi_result" => array(
		"album" => "Lucide (EP)",
		"artist" => "Disiz",
		"year" => 2012,
		"month" => "2012",
		"day" => "26"
	)
);

echo json_encode(findOniTunes($items["one_result2"]));