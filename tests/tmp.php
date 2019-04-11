<?php

include dirname(__DIR__) . '/config.php';
set_time_limit(0);
ini_set("max_execution_time", -1);

define('MAX_FILE_SIZE', 60000000);
header("Content-type:text/html");

$json = file_get_contents(dirname(__DIR__) . "/data/albums.json");
$results = json_decode($json, true);

$x = array();
$s = array();
$m = array();

$replacements = (object) array(
	"search" => array(
		REGEX_ALBUM_BRACKETS,
		"/\\$/",
		"/\€/",
		REGEX_ONLY_ALPHANUMERIC //"/[^A-Za-z0-9 ]/"
	),
	"replace" => array(
		"",
		"S",
		"E",
		""
	)
);
// \s+\((?<content>[^()]*((?!b\.?o\.?)|(?!bande original)|(?!best(-|\s)?of)|(?!vol(ume)?)|(?!(e|é)dition)|(?!album)|(?!version)|(?!bootleg)|(?!chapitre)|(?!compil)|(?!tape)|(?!attendant)|(?!digital)|(?!en route)|(?!ep)|(?!hors(-|\s)?)|(?!live)|(?!maxi)|(?!part)|(?!mix)|(?!saison)|(?!sp(e|é)cial)|(?!cd)|(?!street)|(?!ultime)|(?!deluxe)|(?!collect)|(?!(e|é)pisode))[^()]*)\)$

$array = array(
	"default" => array(
		"term" => "XXX",
		"string" => "XXX",
		"actif" => true
	),
	"album" => array(
		"term" => "Album",
		"string" => "L'album",
		"actif" => true
	),
	//
	"bo" => array(
		"term" => "B.O.",
		"string" => "la B.O.",
		"regex" => "/(b\.?o\.?|bande original)/",
		"actif" => true
	),
	"best_of" => array(
		"term" => "Best-Of",
		"string" => "le best-of",
		"regex" => "/best(-|\s)?of/",
		"actif" => true
	),
	"ep" => array(
		"term" => "EP",
		"string" => "l'EP",
		"regex" => "/e\.?p\.?/",
		"actif" => true
	),
	"live" => array(
		"term" => "Album live",
		"string" => "L'album live",
		"actif" => true
	),
	"maxi" => array(
		"term" => "maxi",
		"string" => "Le maxi",
		"regex" => "/maxi/",
		"actif" => true
	),
	"street_album" => array(
		"term" => "Street album",
		"string" => "Le street album",
		"regex" => "/street(?:\s+album)?/",
		"actif" => true
	),
	"XXX" => array(
		"term" => "XXX",
		"string" => "XXX",
		"regex" => "XXX",
		"actif" => true
	)
);

/*//define('DEFAULT_ROLES', $array);
const DEFAULT_ROLES = $array;

echo json_encode(DEFAULT_ROLES);exit;*/

/*

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
vol(ume)?
(?!r(?!e|é)?)?(e|é)dition
version
chapitre
part
saison
deluxe

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

/*$a = "Le Monde de demain (Maxi)";
preg_match(REGEX_ALBUM_BRACKETS, strtolower($a), $m);
var_dump($m);
exit;*/

foreach ($results as $year => $entities) {
	foreach ($entities as $i => $album) {

		if (preg_match(REGEX_ALBUM_BRACKETS, strtolower($album["album"]), $m)) {

			$s["special"][] = $album["album"];

			// récupération de l'intérieur des parenthèses
			if (!isset($s["brackets"]) || !in_array($m["content"], $s["brackets"])) {
				$s["brackets"][] = $m["content"];
			} //

		} else {
			$s["normal"][] = $album["album"];
		}
		continue;


		/*$h = preg_replace($replacements->search, $replacements->replace, $album["album"]);
		$h = array_map('ucfirst', explode(" ", strtolower($h)));
		$h = implode("", $h);
		echo "#" . $h . "<br/>";
		continue;



		if (preg_match("/\((?<cont>.*)\)$/", $album["album"], $m) && false) {
			//print_r(strtolower($m["cont"]));continue;
			$x[] = strtolower($m["cont"]);
			$s[] = $album["album"];
			//$s[] = preg_replace(REGEX_ALBUM_BRACKETS, "", strtolower($album["album"]));

		} else if (preg_match(REGEX_ALBUM_BRACKETS, strtolower($album["album"]), $m)) {
			continue;
			$s["a"][] = $album["album"];
			$s["r"][] = preg_replace(REGEX_ALBUM_BRACKETS, '', $album["album"]);
			$h = preg_replace($replacements->search, $replacements->replace, $album["album"]);
			$s["h"][] = array_map('ucfirst', explode(" ", strtolower($h)));
			//$s["h"][] = "#" . implode("", array_map('ucfirst', explode(" ", strtolower(preg_replace($replacements->search, $replacements->replace, $album["album"])))));
		} else {
			$s["k"][] = $album["album"];
		}*/
	}
}

//sort($x);
//echo json_encode($x);
echo json_encode($s);
//echo json_encode(array_unique($x));

/*

/\(.*((b\.?o\.?)|(bande original)|(best(-|\s)?of)|(vol(ume)?)|((e|é)dition)|(album)|(version)|(bootleg)|(chapitre)|(compil)|(tape)|(attendant)|(digital)|(en route)|(ep)|(hors(-|\s)?)|(live)|(maxi)|(part)|(mix)|(saison)|(sp(e|é)cial)|(cd)|(street)|(ultime)|(deluxe)|(collect)|((e|é)pisode)).*\)/gmi

/\(.*((?!b\.?o\.?)|(?!bande original)|(?!best(-|\s)?of)|(?!vol(ume)?)|(?!(e|é)dition)|(?!album)|(?!version)|(?!bootleg)|(?!chapitre)|(?!compil)|(?!tape)|(?!attendant)|(?!digital)|(?!en route)|(?!ep)|(?!hors(-|\s)?)|(?!live)|(?!maxi)|(?!part)|(?!mix)|(?!saison)|(?!sp(e|é)cial)|(?!cd)|(?!street)|(?!ultime)|(?!deluxe)|(?!collect)|(?!(e|é)pisode)).*\)/gmi

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