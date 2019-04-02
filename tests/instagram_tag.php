<?php

include dirname(__DIR__) . '/config.php';
header("Content-type:text/html");

use \Bot\Album;
use \Bot\Post\TwitterPost;
use \Bot\Post\InstagramPost;
use \InstagramAPI\Instagram;

// must be at false if you're not using this via a browser
Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

// connexion
$ig = new Instagram();
try { // connexion
    $ig->login($_ENV["INSTAGRAM_USERNAME"], $_ENV["INSTAGRAM_PASSWD"]);
} catch (\Exception $e) {
    echo 'Something went wrong (1): ' . $e->getMessage() . "\n";
    exit(0);
}

/////////////////////////////////////
// recherche
$query = "@keryjamesofficial";
$search = $ig->people->search($query);

if ($search->getNumResults()) {
    /* @var User $user */
    foreach ($search->getUsers() as $user) {
    	if ($user->getUsername() === str_replace("@", "", $query)) {
			//echo json_encode(array(array("pk" => $user->getPk(), "username" => $user->getUsername()), $user));
			break;
			//
    	}
    }
}

// pk kery = 3907142759

////////////////////////////////////
// user_tag
$mediaId = "2013182025464633099";
$mediaId = "2013182025464633099_11945724859";

$metadata = array(
	"usertags" => array(
		"in" => array(
			array(
				'position' => array(0.5, 0.5),
				'user_id'  => '3907142759'
			)
		)
	)
);

echo json_encode($ig->media->edit($mediaId, "L'album \"92-2012\" de Kery James sortait il y a 7 ans.\n\n@keryjamesofficial #922012 #KeryJames", $metadata));

// like : {status: ok} echo json_encode($ig->media->like($mediaId));
exit;