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

// GET /search
// 	:q The term to search for
$genius_artist_id = null;
$genius_search_params = "&q=${q}"; //&per_page={{max_results}}"
$url_search = $genius_api . "/search?access_token=" . $_ENV['GENIUS_CLIENT_ACCESS_TOKEN'] . $genius_search_params;
$json_search = get($url_search);
$query = json_decode($json_search, true);
if ($query["meta"]["status"] === 200) {
	$response = $query["response"];
	//echo $json;

	// aucun résultat correspondant
	if (!count($response["hits"])) {
		echo "[Erreur] Aucun résultat";
		exit;
	}

	foreach ($response["hits"] as $hit) {
		$result = $hit["result"];

		if (preg_match("/" . strtolower($artist_name) . "/", strtolower($result["primary_artist"]["name"]))) {
			$genius_artist_id = $result["primary_artist"]["id"];
			//echo json_encode($result["primary_artist"]);
			break;
		}
	}

	if (!$genius_artist_id) {
		echo "artist non trouvé.";
		exit;
	}

	//echo $genius_artist_id;

} else {
	echo "[Erreur] code: " . $query["meta"]["status"];
	exit;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

// GET /artists :id
// 	:id ID of the artist 
// 	:text_format Format for text bodies related to the document. One or more of dom, plain, and html, separated by commas (defaults to dom). 
$artist_socials = null;
$genius_artist_params = "&";
$url_artist = $genius_api . "/artists/${genius_artist_id}?access_token=" . $_ENV['GENIUS_CLIENT_ACCESS_TOKEN'] . $genius_artist_params;
$json_artist = get($url_artist);
$query = json_decode($json_artist, true);
//echo $json_artist;exit;
if ($query["meta"]["status"] === 200) {
	$artist = $query["response"]["artist"];
	$artist_socials = array(
		"facebook" => $artist["facebook_name"],
		"twitter" => $artist["twitter_name"],
		"instagram" => $artist["instagram_name"]
	);
} else {
	echo "[Erreur] code: " . $query["meta"]["status"];
	exit;
}

echo json_encode($artist_socials);

//response->annotation->body->dom->(tag=p)children->(tag=p)children->(tag=img)attributes->src