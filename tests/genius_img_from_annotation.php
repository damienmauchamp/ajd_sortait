<?php

include dirname(__DIR__) . '/config.php';
header("Content-type:text/html");

// init
$genius_api = "https://api.genius.com";

// préparation de la recherche
$id_annotation = "9169440";


// test function
$tag = 'img'; // what I'm loking for

function findGeniusArtwork($dom) {
	$prev = false;
	$res = [];
	array_walk_recursive($dom, function($value, $key) use (&$prev, &$res){
		if ($key === "tag" && $value === "img") {
			$prev = true;
		} else if ($prev && $key === "src") {
			$res[] = $value;
			return;
		}
	});
	return isset($res[0]) ? $res[0] : null;
}

// TODO : class

// GET /annotations/:id
// 	:id ID of the annotation
// 	:text_format Format for text bodies related to the document. One or more of dom, plain, and html, separated by commas (defaults to dom).
$img = null;
$text_format = "dom";
$genius_params = "&text_format=$text_format";
$url = $genius_api . "/annotations/${id_annotation}?access_token=" . $_ENV['GENIUS_CLIENT_ACCESS_TOKEN'] . $genius_params;
$json = get($url);
$query = json_decode($json, true);
if (intval($query["meta"]["status"]) === 200) {
	$response = $query["response"];
	$body = $response["annotation"]["body"];

	$dom = isset($body["dom"]) ? $body["dom"] : null;
	$html = isset($body["html"]) ? $body["html"] : null;
	$plain = isset($body["plain"]) ? $body["plain"] : null;


	$img = findGeniusArtwork($dom);
	/*
	if ($dom) {
		if ($dom["tag"] === "root") {
			$root = $dom["children"];
			foreach ($dom["children"] as $root_child) {
				if (isset($root_child["tag"]) && $root_child["tag"] === "p") {
					foreach ($root_child["children"] as $p_child) {
						if (isset($p_child["tag"]) && $p_child["tag"] === "img") {
							$img = $p_child["attributes"];
							break 2;
						}
					}
				}
			}
		}
	}*/
} else {
	echo "[Erreur] code: " . $query["meta"]["status"];
	exit;
}

echo json_encode($img);


//response->annotation->body->dom->(tag=p)children->(tag=p)children->(tag=img)attributes->src
