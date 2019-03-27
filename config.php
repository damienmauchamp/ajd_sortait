<?php

ini_set('max_execution_time', 0);
header("Content-type:application/json");

require_once __DIR__ . '/vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use \InstagramAPI\Instagram;

\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

if (is_file(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::create(__DIR__);
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
$regexEP = "/\s+\(EP\)$/";
$file_prefixe = "albums_";
$img_dir = '/img/';
$img = (object)array(
    'dir' => $img_dir,
    'path' => __DIR__ . $img_dir,
    'ext' => '.jpg',
    'width' => '1800'
);

function generateHashtags($item)
{
    return implode(" ", array(
        "#" . implode("", explode(" ", removeNonHashtagCharacters($item["album"]))), // album
        "#" . implode("", explode(" ", removeNonHashtagCharacters($item["artist"]))) // artist
    ));
}

function getCaption($item) {
    global $regexEP;
    $name = $item["album"];
    $artist = ($item["artist"] !== "Artistes multiples" || $item["artist"] !== "Various Artists" || $item["artist"] !== "Multi-interprètes") ? $item["artist"] : "";
    $year = $item["year"];
    $old = date("Y") - intval($year);

    $caption = "L'album ";
    if (preg_match($regexEP, $item["album"])) {
        $caption = "L'EP ";
    } else if (preg_match("/compilation/", strtolower($item["album"]))) {
        $caption = "La compilation ";
    }

    $caption .= "\"${name}\" ". ($artist !== '' ? "de ${artist} " : "") ."sortait il y a ${old} an" . ($old > 1 ? "s" : "") . ".";
    return $caption;
}

function removeNonHashtagCharacters($str)
{
    return str_replace(array("-", " ", ".", "'", "\""), "", $str);
}

function remove_accents($string) {
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    $chars = array(
    // Decompositions for Latin-1 Supplement
    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
    chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
    chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
    chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
    chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
    chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
    chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
    chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
    chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
    chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
    chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
    chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
    chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
    chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );

    $string = strtr($string, $chars);

    return $string;
}

function toPureString($str) {
    return trim(strtolower(removeNonHashtagCharacters(remove_accents(preg_replace('/[^(\x20-\x7F)]*/','', $str)))));
}

function twitterPost($item)
{
    $artwork = __DIR__ . $item["artwork"];
    $caption = getCaption($item);
    $hashtags = generateHashtags($item);

    if ($_ENV["ENVIRONMENT"] === "dev") {
        return realpath($artwork);
    }

    $connection = new TwitterOAuth($_ENV["TWITTER_API_KEY"], $_ENV["TWITTER_API_SECRET_KEY"], $_ENV["TWITTER_ACCESS_TOKEN"], $_ENV["TWITTER_ACCESS_TOKEN_SECRET"]);
    $media = $connection->upload('media/upload', array('media' => $artwork));
    $parameters = [
        'status' => $caption . "\n\n" . $hashtags,
        'media_ids' => implode(',', [$media->media_id_string])
    ];
    return $connection->post('statuses/update', $parameters);
}


function findArtistInstagramUsername($query, $ig = null, $minLength = 5) {
    $return = null;

    if (strlen($query) < $minLength) {
        return $return;
        exit;
    }

    if (!$ig) {
        $ig = new Instagram();
        try { // connexion
            $ig->login($_ENV["INSTAGRAM_USERNAME"], $_ENV["INSTAGRAM_PASSWD"]);
        } catch (\Exception $e) {
            echo 'Something went wrong (1): ' . $e->getMessage() . "\n";
            exit(0);
        }
    }

    $search = $ig->people->search($query);
    $query_pure = toPureString($query);

    if ($search->getNumResults()) {
        //var_dump($search->getUsers());
        /* @var User $user */
        foreach ($search->getUsers() as $user) {
            $fullname_pure = toPureString($user->getFullName());
            $username_pure = toPureString($user->getUsername());

            $literal_match = $query_pure === $fullname_pure || $query_pure === $username_pure;
            $preg_match = preg_match("/(${query_pure})/", $fullname_pure) || preg_match("/${query_pure}/", $username_pure);

            if ($user->getIsVerified() && $preg_match) {

                $debug = array(
                    "query" => $query,
                    "result" => array(
                        "pk" => $user->getPk(),
                        "username" => $user->getUsername(), 
                        "full_name" => $user->getFullName(), 
                        "verified" => $user->getIsVerified()
                    ),
                    "test" => array(
                        "query" => $query_pure, 
                        "fullname" => $fullname_pure,
                        "username" => $username_pure, 
                        "match" => $literal_match, 
                        "preg_match" => $preg_match
                    )
                );

                $return = array("pk" => $user->getPk(), "username" => $user->getUsername());
                break;
            }
        }
    }

    return $return;
}

function instagramPost($item)
{
    $artwork = __DIR__ . $item["artwork"];
    $caption = getCaption($item);
    $hashtags = generateHashtags($item);

    if ($_ENV["ENVIRONMENT"] === "dev") {
        return realpath($artwork);
    }

    $ig = new Instagram();

    try { // connexion
        $ig->login($_ENV["INSTAGRAM_USERNAME"], $_ENV["INSTAGRAM_PASSWD"]);
    } catch (\Exception $e) {
        echo 'Something went wrong (1): ' . $e->getMessage() . "\n";
        exit(0);
    }

    //$user = findArtistInstagramUsername($item["artist"]);
    // TODO : tag user

    try { // publication
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
    global $regexEP;

    $title = preg_replace($regexEP, "", albumStrFix($album["album"]));
    $artist = albumStrFix($album["artist"]);
    $year = $album["year"];
    $month = $album["month"];
    $day = $album["day"];

    //echo albumStrFix($album["artist"]) . " " . preg_replace($regexEP, "", albumStrFix($album["album"]));

    if ($req = get("https://itunes.apple.com/search?entity=album&country=fr&limit=100&term=" . trim(urlencode($artist . " " . $title)))) {
        $reponse = json_decode($req, true);
        if (intval($reponse["resultCount"]) === 0) { // pour l'instant, à changer (vérif date, copyright, artists, etc)
            return false;
        } else if (intval($reponse["resultCount"]) === 1) {
            return array("artworkUrl100" => $reponse["results"][0]["artworkUrl100"]); // pour l'instant, à changer (vérif date, copyright, artists, etc)
        } else { // $reponse["resultCount"]) > 0 

            // album name is the perfect match
            foreach ($reponse["results"] as $i => $item) {
                if ($title === $item["collectionName"]) {
                    return array("artworkUrl100" => $item["artworkUrl100"]);
                }
            }

            // release date perfect match
            foreach ($reponse["results"] as $i => $item) {
                if (preg_match("/^$year-$month-$day/", $item["releaseDate"])) {
                    return array("artworkUrl100" => $item["artworkUrl100"]);
                }
            }

            // release date perfect match year-month
            foreach ($reponse["results"] as $i => $item) {
                if (preg_match("/^$year-$month/", $item["releaseDate"])) {
                    return array("artworkUrl100" => $item["artworkUrl100"]);
                }
            }

            // copyright match
            foreach ($reponse["results"] as $i => $item) {
                if (preg_match("/^℗ $year /", $item["copyright"])) {
                    return array("artworkUrl100" => $item["artworkUrl100"]);
                }
            }

            return array("artworkUrl100" => $reponse["results"][0]["artworkUrl100"]);
        }
    }
    return false;
}

function albumStrFix($str) {
    return str_replace(array("Artistes multiples", "Various Artists", "Multi-interprètes", "Compilation", "compilation", "\""), "", str_replace("&amp;", "&", $str));
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
    return file_put_contents(__DIR__ . "/data/$name.json", json_encode($content));
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