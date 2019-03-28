<?php

include dirname(__DIR__) . '/config.php';
header("Content-type:text/html");
use \InstagramAPI\Instagram;

// must be at false if you're not using this via a browser
Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

$query = isset($_GET['q']) ? $_GET['q'] : "";
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$res = findArtistInstagramUsername($query, $year);

echo json_encode($res);