<?php
// INIT 8h30
// cron : 30 8 * * *	php script_init.php
include dirname(__DIR__) . '/config.php';

use Sunra\PhpSimple\HtmlDomParser;

define('MAX_FILE_SIZE', 60000000);

$final = $albums = array();

for ($year = $anneeStart; $year <= $anneeEnd; $year++) {
    if (1985 <= $year && $year < 1990)
        continue;

    // get html
    //$url = get("https://genius.com/Rap-francais-discographie-$year-lyrics");
    // albums with known releases date
    //preg_match_all($regex_with_id, $url, $albumsMatches, PREG_SET_ORDER, 0);
    //$albums = getAlbumsMatches($albumsMatches, $year);
    // albums with unknown releases date
    //preg_match_all($uRegex, $url, $unknownAlbumsMatches, PREG_SET_ORDER, 0);
    //$unknownAlbums = getUnknownAlbumsMatches($unknownAlbumsMatches, $year);


    $url = "https://genius.com/Rap-francais-discographie-$year-lyrics";

    //

    $html = cleanString($genius->getSongsResource()->getSongLyrics($url, true));
    $dom = HtmlDomParser::str_get_html($html);

    $raw = ""; // for unknown

    foreach ($dom->find('.lyrics') as $element) {
        preg_match_all($regex_with_id, $element->innertext, $albumsMatches, PREG_SET_ORDER, 0);
        $raw .= htmlspecialchars_decode($element->plaintext);
    }

    $albums = getAlbumsMatches($albumsMatches, $year);

    preg_match_all($uRegex, $raw, $unknownAlbumsMatches, PREG_SET_ORDER, 0);
    $unknownAlbums = getUnknownAlbumsMatches($unknownAlbumsMatches, $year);

    $final[$year] = array_merge(isset($albums[$year]) ? $albums[$year] : array(), isset($unknownAlbums[$year]) ? $unknownAlbums[$year] : array());
}

$today = getTodaysAlbums($final);

writeJSONFile("albums", $final);
writeJSONFile($file_prefixe . date("Ymd"), $today);
echo json_encode($today);