<?php

/**
 * @todo split in methods
 * @todo add the possibility to run only one script (genius/artworks/infos)
 * @todo log every steps
 */

// INIT 8h30
// cron : 30 8 * * *	php script/init.php

// includes
include dirname(__DIR__).'/config.php';

global $genius;

// redefine max file size
const MAX_FILE_SIZE = 60000000;

// dependencies
// use Sunra\PhpSimple\HtmlDomParser;
use \KubAT\PhpSimple\HtmlDomParser;

// instances initiation
$final = $albums = array();

// loop fetching every albums
for($year = YEAR_START; $year <= YEAR_END; $year++) {

	// if (!in_array($year, ['1990'])) {
	//     continue;
	// }
	// echo "$year\n";

	// pages don't exist between 1985-89
	// if (1985 <= $year && $year < 1990)
	//     continue;

	// "song" URL
	$url = "https://genius.com/Rap-francais-discographie-$year-annotated?react=1";

	// scrapping lyrics' html and parsing it
	$html = cleanString($genius->getSongsResource()->getSongLyrics($url, true));
	// print_r('strlen($html):' . strlen($html) . "\n");
	$dom = HtmlDomParser::str_get_html($html);

	// print_r('$dom');
	// echo "\n";
	// print_r(gettype($dom));
	// echo "\n\n";

	// print_r('$dom->find(div[class=lyrics])');
	// echo "\n";
	// print_r(gettype($dom->find('div[class=lyrics]')));
	// print_r(gettype($dom->find('div[class=lyrics]', 0)));
	// echo "\n";
	// print_r(count($dom->find('div[class=lyrics]')));
	// echo "\n\n";

	// upcoming plaintext for unknown albums
	$raw = "";

	// fetching matches
	$albums_matches = $unknown_albums_matches = [];
	// $str = str_replace('‪', '', $dom->innertext);
	// preg_match_all(REGEX_ALBUM_GENIUS_HTML, $str, $albums_matches, PREG_SET_ORDER, 0);
	// $raw = htmlspecialchars_decode($dom->plaintext);

	// file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_dom.log", print_r($dom, true));
	// file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_lyrics.log", print_r($dom->find('div.lyrics'), true));
	// file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_lyrics2.log", print_r($dom->find('div[class=lyrics]'), true));
	// foreach ($dom->find('body') as $element) {
	// foreach ($dom->find('html') as $element) {
	// foreach ($dom->find('div[class=lyrics]') as $element) {

	$elements = $dom->find('.lyrics');
	if(!$elements) {
		$elements = $dom->find('div[class=lyrics]', 0);
		if(!$elements) {
			$elements = $dom->find('body');
		}
	}

//	print_r($elements);
//	dd();

	foreach($elements as $element) {
		// print_r(strlen($element->innertext) . "\n");
		// print_r( $element);
		$str = str_replace('‪', '', $element->innertext);

		// print_r('strlen($str):' . strlen($str) . "\n");
		// print_r('$str:' . $str . "\n");

		// print_r(strlen($str) . "\n");

		// $tmp = explode('head>', $str);
		// print_r('c:' . count($tmp) . "\n");
		// $str = count($tmp) > 2 ? $tmp[2] : $tmp[0];

		$tmp = explode('<body', $str);
		// print_r('c:' . count($tmp) . "\n");
		$str = count($tmp) > 1 ? $tmp[1] : $tmp[0];

		$tmp = explode('__PRELOADED_STATE__', $str);
		// print_r('c:' . count($tmp) . "\n");
		$str = $tmp[0];

		file_put_contents('test.log', $str);


		// print_r(strlen($str) . "\n");

		preg_match_all(REGEX_ALBUM_GENIUS_HTML, $str, $albums_matches, PREG_SET_ORDER, 0);
		// file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_albums_matches.log", print_r($albums_matches, true), FILE_APPEND);
		$raw .= preg_replace('/(About.*$)/', '', htmlspecialchars_decode($element->plaintext));
	}

	// creation of an assoc array with the year and the albums matches
	$albums = getAlbumsMatches($albums_matches, $year);
	// file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_albums.log", print_r($albums, true));

	// fetching all unknown albums
	preg_match_all(REGEX_UNKNOWN_ALBUM_GENIUS, $raw, $unknown_albums_matches, PREG_SET_ORDER, 0);
	// file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_unknown_albums_matches.log", print_r($unknown_albums_matches, true));
	file_put_contents(DIR_LOGS_GENIUS.'/genius_'.date('Ymd')."_{$year}_raw.log", print_r($raw, true));
	// creation of an assoc array with the year and the unknown albums matches
	$unknownAlbums = getAlbumsMatches($unknown_albums_matches, $year);

	// merging arrays for this year
	$final[$year] = array_merge(isset($albums[$year]) ? $albums[$year] : array(), isset($unknownAlbums[$year]) ? $unknownAlbums[$year] : array());
	// break;
}

$today = getTodaysAlbums($final);

writeJSONFile("albums", $final);
writeJSONFile(PREFIX_ALBUM_FILE.date("Ymd"), $today);
echo json_encode([
	'today' => $today,
	'final' => $final,
]);