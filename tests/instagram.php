<?php

include dirname(__DIR__) . '/config.php';
header("Content-type:text/html");

use \Bot\Album;
//use \Bot\Post\TwitterPost;
use \Bot\Post\InstagramPost;

$album = array(
    "date" => 1,
    "day" => "01",
    "month" => "04",
    "year" => 1991,
    "artist" => "IAM",
    "album" => "Red black green (EP)",
    "annotation_id" => "2652699",
    "artwork" => "/img/IAM+Red+black+green+%28EP%29.jpg",
    "posted" => false,
    "post_date" => 1554112800
);

$item = new Album($album);
$instagram = new InstagramPost($item);
var_dump($instagram);