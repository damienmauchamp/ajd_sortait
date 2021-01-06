<?php

// INIT 8h30
// cron : 30 8 * * *	php script/init.php

// includes
include dirname(__DIR__) . '/config.php';

// redefine max file size
define('MAX_FILE_SIZE', 60000000);

// dependencies
use Sunra\PhpSimple\HtmlDomParser;

// instances initiation
$final = $albums = array();

// loop fetching every albums
for ($year = YEAR_START ; $year <= YEAR_END ; $year++) {

    if (!in_array($year, ['1990'])) {
        continue;
        // (?:[^;\/]+\s*(\*|\-))
    }
    echo "$year\n";

    // pages don't exist between 1985-89
    // if (1985 <= $year && $year < 1990)
    //     continue;

    // "song" URL
    $url = "https://genius.com/Rap-francais-discographie-$year-annotated";

    // scrapping lyrics' html and parsing it
    $html = cleanString($genius->getSongsResource()->getSongLyrics($url, true));
    $dom = HtmlDomParser::str_get_html($html);

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
    foreach ($dom->find('html') as $element) {
    // foreach ($dom->find('.lyrics') as $element) {
        print_r(strlen($element->innertext) . "\n");
        $str = str_replace('‪', '', $element->innertext);

        print_r(strlen($str) . "\n");

        // $tmp = explode('head>', $str);
        // print_r('c:' . count($tmp) . "\n");
        // $str = count($tmp) > 2 ? $tmp[2] : $tmp[0];


        // $tmp = explode('<body', $str);
        // print_r('c:' . count($tmp) . "\n");
        // $str = count($tmp) > 1 ? $tmp[1] : $tmp[0];


        $tmp = explode('</lyrics', $str);
        print_r('c:' . count($tmp) . "\n");
        $str = $tmp[0];


        print_r(strlen($str) . "\n");

        preg_match_all(REGEX_ALBUM_GENIUS_HTML, $str, $albums_matches, PREG_SET_ORDER, 0);
        // file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_albums_matches.log", print_r($albums_matches, true), FILE_APPEND);
        $raw .= preg_replace('/(About.*$)/', '', htmlspecialchars_decode($element->plaintext));
    }

    // creation of an assoc array with the year and the albums matches
    $albums = getAlbumsMatches($albums_matches, $year);
    print_r($albums);
    // file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_albums.log", print_r($albums, true));

    // fetching all unknown albums
    preg_match_all(REGEX_UNKNOWN_ALBUM_GENIUS, $raw, $unknown_albums_matches, PREG_SET_ORDER, 0);
    // file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_unknown_albums_matches.log", print_r($unknown_albums_matches, true));
    // file_put_contents(dirname(__DIR__) . '/logs/genius_' . date('Ymd') . "_{$year}_raw.log", print_r($raw, true));
    // creation of an assoc array with the year and the unknown albums matches
    $unknownAlbums = getAlbumsMatches($unknown_albums_matches, $year);

    // merging arrays for this year
    $final[$year] = array_merge(isset($albums[$year]) ? $albums[$year] : array(), isset($unknownAlbums[$year]) ? $unknownAlbums[$year] : array());
    // break;
}

$today = getTodaysAlbums($final);

writeJSONFile("albums", $final);
writeJSONFile(PREFIX_ALBUM_FILE . date("Ymd"), $today);
echo json_encode($today);