<?php

include dirname(__DIR__) . '/config.php';
header("Content-type:text/html");

// init
$genius_api = "https://api.genius.com";

// préparation de la recherche
$artist_name = "TLF";
$album_name = "No Limit";
$q = urlencode("${artist_name} ${album_name}");

// TODO : class


//echo $json;exit;

// GET /annotations/:id
// 	:id ID of the annotation
// 	:text_format Format for text bodies related to the document. One or more of dom, plain, and html, separated by commas (defaults to dom).
