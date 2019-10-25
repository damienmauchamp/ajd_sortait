<?php

// 0 psy 4
$id = 0;
$data[$id] = (object) array(
	'id' => $id,
	'name' => "Psy 4 de la Rime",
	'genius' => (object) array(
		'id' => 15855,
		'artistName' => "Psy 4 de la Rime"
	),
	'itunes' => (object) array(
		'id' => 79821216,
		'artistName' => "Psy 4 de la Rime"
	),
	'instagram' => (object) array(
		'id' => 195181418,
		'username' => "psy4officiel"
	),
	'twitter' => (object) array(
		'id' => 263769652,
		'username' => "PSY4OFFICIEL"
	),
	'band' => (object) array(
		'members' => [1, 3, 4, 5],
		'part_of' => []
	),
	'updates' => (object) array(
		'auto' => new DateTime(),
		'manually' => false
	)
);

// 1 soprano
$id = 1;
$data[$id] = (object) array(
	'id' => $id,
	'name' => "Soprano",
	'genius' => (object) array(
		'id' => 1431,
		'artistName' => "Soprano"
	),
	'itunes' => (object) array(
		'id' => 62797922,
		'artistName' => "Soprano"
	),
	'instagram' => (object) array(
		'id' => 187728200,
		'username' => "sopranopsy4"
	),
	'twitter' => (object) array(
		'id' => 128614917,
		'username' => "sopranopsy4"
	),
	'band' => (object) array(
		'members' => [],
		'part_of' => [0]
	),
	'updates' => (object) array(
		'auto' => new DateTime(),
		'manually' => false
	)
);

// 2 passi
$id = 2;
$data[$id] = (object) array(
	'id' => $id,
	'name' => "Passi",
	'genius' => (object) array(
		'id' => 13095,
		'artistName' => "Passi"
	),
	'itunes' => (object) array(
		'id' => 15048020,
		'artistName' => "Passi"
	),
	'instagram' => (object) array(
		'id' => 191529930,
		'username' => "passi_issap"
	),
	'twitter' => (object) array(
		'id' => 615669266,
		'username' => "passi_issap"
	),
	'band' => (object) array(
		'members' => [],
		'part_of' => [0]
	),
	'updates' => (object) array(
		'auto' => new DateTime(),
		'manually' => false
	)
);

// 3 alonzo
$id = 3;
$data[$id] = (object) array(
	'id' => $id,
	'name' => "Alonzo",
	'genius' => (object) array(
		'id' => 13711,
		'artistName' => "Alonzo"
	),
	'itunes' => (object) array(
		'id' => 532788825,
		'artistName' => "Alonzo"
	),
	'instagram' => (object) array(
		'id' => 179779724,
		'username' => "alonzopsy4"
	),
	'twitter' => (object) array(
		'id' => 144583125,
		'username' => "alonzopsy4"
	),
	'band' => (object) array(
		'members' => [],
		'part_of' => [0]
	),
	'updates' => (object) array(
		'auto' => new DateTime(),
		'manually' => false
	)
);

// 4 vincenzo
$id = 4;
$data[$id] = (object) array(
	'id' => $id,
	'name' => "Vincenzo",
	'genius' => (object) array(
		'id' => 20736,
		'artistName' => "Vincenzo"
	),
	'itunes' => (object) array(
		'id' => 981082591,
		'artistName' => "Vincenzo"
	),
	'instagram' => (object) array(
		'id' => 184210390,
		'username' => "vincenzopsy4"
	),
	'twitter' => (object) array(
		'id' => 138585503,
		'username' => "vincenzopsy4"
	),
	'band' => (object) array(
		'members' => [],
		'part_of' => [0]
	),
	'updates' => (object) array(
		'auto' => new DateTime(),
		'manually' => false
	)
);

// 5 syastyles
$id = 5;
$data[$id] = (object) array(
	'id' => $id,
	'name' => "Sya Styles",
	'genius' => (object) array(
		'id' => 43454,
		'artistName' => "Sya Styles"
	),
	'itunes' => (object) array(
		'id' => 79821212,
		'artistName' => "Sya Styles"
	),
	'instagram' => null,
	'twitter' => null,
	'band' => (object) array(
		'members' => [],
		'part_of' => [0]
	),
	'updates' => (object) array(
		'auto' => new DateTime(),
		'manually' => false
	)
);

// 6 test_genius
$id = 6;
$data[$id] = (object) array(
	'id' => $id,
	'name' => "ABC",
	'genius' => (object) array(
		'id' => -1,
		'artistName' => "azerty"
	),
	'itunes' => (object) array(
		'id' => 79821212,
		'artistName' => "qwerty"
	),
	'instagram' => null,
	'twitter' => null,
	'band' => null,
	'updates' => (object) array(
		'auto' => new DateTime(),
		'manually' => false
	)
);

writeJSONFile("socials", $data);