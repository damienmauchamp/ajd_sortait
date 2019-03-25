<?php
// INIT 8h30
// cron : 30 8 * * *	php script_init.php
include 'start.php';

$final = $albums = array();

for ($year = $anneeStart; $year <= $anneeEnd; $year++) {
    if (1985 <= $year && $year < 1990)
        continue;
    $url = get("https://genius.com/Rap-francais-discographie-$year-lyrics");
    preg_match_all($regex, $url, $albumsMatches, PREG_SET_ORDER, 0);
    $albums = getAlbumsMatches($albumsMatches, $year);
    preg_match_all($uRegex, $url, $unknownAlbumsMatches, PREG_SET_ORDER, 0);
    $unknownAlbums = getUnknownAlbumsMatches($unknownAlbumsMatches, $year);
    $final[$year] = array_merge(isset($albums[$year]) ? $albums[$year] : array(), isset($unknownAlbums[$year]) ? $unknownAlbums[$year] : array());
}

$today = getTodaysAlbums($final);

writeJSONFile("albums", $final);
writeJSONFile($file_prefixe . date("Ymd"), $today);
echo json_encode($today);