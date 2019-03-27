<?php

include dirname(__DIR__) . '/config.php';

$items = array(
	array(
		"album" => "Touche d'Espoir",
		"artist" => "Assassin",
		"year" => 2000,
		"month" => "03",
		"day" => "27"
	),
	array(
		"album" => "Extra-Lucide",
		"artist" => "Disiz",
		"year" => 2012,
		"month" => "10",
		"day" => "29"
	),
	array(
		"album" => "Lucide (EP)",
		"artist" => "Disiz",
		"year" => 2012,
		"month" => "2012",
		"day" => "26"
	)
);

echo getCaption($items[2]);