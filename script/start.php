<?php

ini_set('max_execution_time', 0);
header("Content-type:application/json");

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use \InstagramAPI\Instagram;

//Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

if (is_file(dirname(__DIR__) . '/.env')) {
    $dotenv = Dotenv\Dotenv::create(dirname(__DIR__));
    $dotenv->load();
}

$allow = (object)array(
    "twitter" => isset($_GET["twitter"]) ? $_GET["twitter"] : true,
    "instagram" => isset($_GET["instagram"]) ? $_GET["instagram"] : true,
);

$anneeStart = 1984;
$anneeEnd = date("Y") - 1;
$heureStart = 10;
$heureEnd = 16;
$regex = "/^(\*|\-) (?<day>(\d|X){2})\/(?<month>\d{2}) \: (?<artist>.*) (-|–) (?<album>.*)$/m"; // mois non connu
$uRegex = "/^(\*|\-) ((?<day>X{2})\/(?<month>X{2}) \: )?(?<inter>Du (?<iday1>(X|\d){2})\/(?<imonth1>(X|\d){2}) au (?<iday2>(X|\d){2})\/(?<imonth2>(X|\d){2}) :)?(?<artist>[^((X|\d{2})\/(X|\d{2}))].*) (- |– )(?<album>.*)$/m";
$file_prefixe = "albums_";
$img_dir = '/img/';
$img = (object)array(
    'dir' => $img_dir,
    'path' => dirname(__FILE__) . $img_dir,
    'ext' => '.jpg',
    'width' => '1000'
);

function generateHashtags($item)
{
    return implode(" ", array(
        "#" . implode("", explode(" ", removeNonHashtagCharacters($item["album"]))), // album
        "#" . implode("", explode(" ", removeNonHashtagCharacters($item["artist"]))) // artist
    ));
}

function removeNonHashtagCharacters($str)
{
    return str_replace(array("-", " ", ".", "'", "\""), "", $str);
}

function twitterPost($item)
{
    $name = $item["album"];
    $artist = $item["artist"];
    $artwork = dirname(__FILE__) . $item["artwork"];
    $year = $item["year"];

    $old = date("Y") - intval($year);

    $caption = "L'album \"${name}\" de ${artist} sortait il y a ${old} an" . ($old > 1 ? "s" : "") . ".";

    $hashtags = generateHashtags($item);

    $connection = new TwitterOAuth($_ENV["TWITTER_API_KEY"], $_ENV["TWITTER_API_SECRET_KEY"], $_ENV["TWITTER_ACCESS_TOKEN"], $_ENV["TWITTER_ACCESS_TOKEN_SECRET"]);
    $media = $connection->upload('media/upload', array('media' => $artwork));
    $parameters = [
        'status' => $caption . "\n\n" . $hashtags,
        'media_ids' => implode(',', [$media->media_id_string])
    ];
    return $connection->post('statuses/update', $parameters);
}

function instagramPost($item)
{
    $name = $item["album"];
    $artist = $item["artist"];
    $artwork = dirname(__FILE__) . $item["artwork"];
    $year = $item["year"];

    $old = date("Y") - intval($year);

    $caption = "L'album \"${name}\" de ${artist} sortait il y a ${old} an" . ($old > 1 ? "s" : "") . ".";

    $hashtags = generateHashtags($item);

    $ig = new Instagram();

    try {
        $ig->login($_ENV["INSTAGRAM_USERNAME"], $_ENV["INSTAGRAM_PASSWD"]);
    } catch (\Exception $e) {
        echo 'Something went wrong (1): ' . $e->getMessage() . "\n";
        exit(0);
    }

    try {
        $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($artwork);
        $ig->timeline->uploadPhoto($photo->getFile(), ['caption' => $caption . "\n\n" . $hashtags]);
    } catch (\Exception $e) {
        echo 'Something went wrong (2): ' . $e->getMessage() . "\n";
    }

}

function getTodaysAlbums($albums)
{
    $todayCount = 0;
    $today = $today_notFound = $thisMonth = array();
    foreach ($albums as $year => $releases) {
        foreach ($releases as $album) {
            if ($album["date"] === 1 && intval($album["month"]) === intval(date("m")) && intval($album["day"]) === intval(date("d"))) {

                if ($entity = findOniTunes($album)) {
                    $img = saveImg($entity["artworkUrl100"], $album["artist"] . " " . $album["album"]);

                    if ($img["response"]) {
                        $album["artwork"] = $img["name"];
                    }

                    $album["posted"] = false;
                    $today[$year][] = $album;
                    $todayCount++;
                } else {
                    $today_notFound[$year][] = $album;
                }

            } else if (intval(date("d")) === 1 && intval($album["month"]) === intval(date("m"))) {
                $thisMonth[$year][] = $album;
            }
        }
    }

    $today = setUpPostingDate($today, $todayCount);

    return array("todayCount" => $todayCount, "today" => $today, "today_notFound" => $today_notFound, "thisMonth" => $thisMonth);
}

function clearImgs()
{
    global $img;

    $files = glob($img->path . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

function setUpPostingDate($today, $todayCount)
{
    global $heureStart, $heureEnd;
    $n_albums = $todayCount;

    // écart en décimales
    $dec = ($heureEnd - $heureStart) / $n_albums;
    $mins = decimalToHours($dec)["mins"];

    // initialisation de la date de post (du premier)
    $date = new DateTime(date('Y-m-d ') . twoDigits($heureStart) . ':00:00');

    foreach ($today as $year => $releases) {
        foreach ($releases as $i => $album) {
            $today[$year][$i]["post_date"] = strtotime($date->format('Y-m-d H:i:s'));
            try {
                $date->add(new DateInterval('PT' . $mins . 'M'));
            } catch (Exception $e) {
            }
        }
    }

    return $today;
}

function decimalToHours($decimaltime)
{
    $hours = floor($decimaltime);
    $seconds = ($decimaltime * 3600);
    $seconds -= $hours * 3600;
    // calculate minutes left
    $minutes = floor($seconds / 60);
    // remove those from seconds as well
    $seconds -= $minutes * 60;

    return array(
        "hh:mm:ss" => twoDigits($hours) . ":" . twoDigits($minutes) . ":" . twoDigits($seconds),
        "mins" => 60 * $hours + $minutes
    );
}

function twoDigits($num)
{
    return (strlen($num) < 2) ? "0{$num}" : $num;
}

function findOniTunes($album)
{
    if ($req = get("https://itunes.apple.com/search?entity=album&country=fr&limit=100&term=" . urlencode($album["artist"] . " " . $album["album"]))) {
        $reponse = json_decode($req, true);
        if (intval($reponse["resultCount"]) === 0) { // pour l'instant, à changer (vérif date, copyright, artists, etc)
            return false;
        } else if (intval($reponse["resultCount"]) === 1) {
            return array("artworkUrl100" => $reponse["results"][0]["artworkUrl100"]); // pour l'instant, à changer (vérif date, copyright, artists, etc)
        } else {
            return array("artworkUrl100" => $reponse["results"][0]["artworkUrl100"]);
        }
    }
    return false;
}

function saveImg($url, $name)
{
    global $img, $img_dir;

    $name = urlencode($name);
    $url = str_replace("100x100bb", $img->width . 'x' . $img->width . 'bb', $url);
    $img_file = $img->path . $name . $img->ext;
    if (!is_dir($img->path))
        mkdir($img->path);
    return array("response" => file_put_contents($img_file, file_get_contents($url)), "name" => $img_dir . $name . $img->ext);
}

function writeJSONFile($name, $content)
{
    return file_put_contents("../data/$name.json", json_encode($content));
}

function getAlbumsMatches($matches, $year)
{
    $entities = array();
    foreach ($matches as $item) {
        $entities[$year][] = array(
            "date" => 1,
            "day" => $item["day"] !== "XX" ? $item["day"] : "",
            "month" => $item["month"],
            "year" => $year,
            "artist" => trim($item["artist"]),
            "album" => trim($item["album"]),
            //"db" => "('".addslashes($item["artist"])."', '".addslashes($item["album"])."', '" . $year . "-" . $item["month"] . "-" . $item["day"] . "'),"
        );
    }
    return $entities;
}

function getUnknownAlbumsMatches($matches, $year)
{
    $entities = array();
    foreach ($matches as $item) {
        if (isset($item["iday1"]) || isset($item["imonth1"]) || isset($item["iday2"]) || isset($item["imonth2"])) {
            $entities[$year][] = array(
                "date" => 3,
                "day" => $item["day"] !== "XX" ? $item["day"] : "",
                "month" => $item["month"],
                "year" => $year,
                "start" => array(
                    "day" => $item["iday1"] !== "XX" ? $item["iday1"] : "",
                    "month" => $item["imonth1"] !== "XX" ? $item["imonth1"] : "",
                ),
                "end" => array(
                    "day" => $item["iday2"] !== "XX" ? $item["iday2"] : "",
                    "month" => $item["imonth2"] !== "XX" ? $item["imonth2"] : "",
                ),
                "artist" => trim($item["artist"]),
                "album" => trim($item["album"])
            );
        } else {
            $entities[$year][] = array(
                "date" => 2,
                "day" => $item["day"] !== "XX" ? $item["day"] : "",
                "month" => $item["month"],
                "year" => $year,
                "artist" => trim($item["artist"]),
                "album" => trim($item["album"])
            );
        }
    }
    return $entities;
}

function get($url)
{
    $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

    $options = array(

        CURLOPT_CUSTOMREQUEST => "GET",        //set request type post or get
        CURLOPT_POST => false,        //set to GET
        CURLOPT_USERAGENT => $user_agent, //set user agent
        CURLOPT_COOKIEFILE => "cookie.txt", //set cookie file
        CURLOPT_COOKIEJAR => "cookie.txt", //set cookie jar
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING => "",       // handle all encodings
        CURLOPT_AUTOREFERER => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT => 120,      // timeout on response
        CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);

    $header['errno'] = $err;
    $header['errmsg'] = $errmsg;
    $header['content'] = strip_tags($content);
    return $header["content"];
}