<?php

include dirname(__DIR__) . '/config.php';
set_time_limit(0);
ini_set("max_execution_time", -1);

define('MAX_FILE_SIZE', 60000000);

$json = file_get_contents(dirname(__DIR__) . "/data/albums.json");
$results = json_decode($json, true);

$x = array();



foreach ($results as $year => $entities) {
	foreach ($entities as $i => $album) {
		if (preg_match("/\((?<cont>.*)\)$/", $album["album"], $m))
			$s[] = preg_replace($r, "", strtolower($album["album"]));
			//$x[] = strtolower($m["cont"]);
	}
}

sort($s);
echo json_encode(array_unique($s));

/**

/\(.*((b\.?o\.?)|(bande original)|(best(-|\s)?of)|(vol(ume)?)|((e|é)dition)|(album)|(version)|(bootleg)|(chapitre)|(compil)|(tape)|(attendant)|(digital)|(en route)|(ep)|(hors(-|\s)?)|(live)|(maxi)|(part)|(mix)|(saison)|(sp(e|é)cial)|(cd)|(street)|(ultime)|(deluxe)|(collect)|((e|é)pisode)).*\)/gmi

/////////////////////////////////

BO
(b\.?o\.?|bande original)

BEST OF
best(-|\s)?of

EP
ep

MAXI
maxi

PROJET
bootleg
compil
tape
digital
(attendant|avant|en route).*album
hors(-|\s)?
mix
sp(e|é)cial
cd
ultime
collect
(e|é)pisode

LIVE
live

ALBUM
street

?
(e|é)dition

/////////////////////////////////

.*XXXX.*

b\.?o\.?
bande original
best(-|\s)?of
vol(ume)?
(e|é)dition
album
version
bootleg
chapitre
compil
tape
attendant
digital
en route
ep
hors(-|\s)?
live
maxi
part
mix
saison
sp(e|é)cial
cd
street
ultime
deluxe
collect
(e|é)pisode

*/
exit;
/*
foreach ($results as $year => $entities) {
	foreach ($entities as $i => $album) {
		if ()
preg_match("/\((?<cont>.*)\)/", $album, $m)*/