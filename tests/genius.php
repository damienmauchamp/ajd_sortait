<?php

include dirname(__DIR__) . '/config.php';
header("Content-type:text/html");

//var_dump(get("https://genius.com/songs/378195"));exit;

//echo $genius->getSearchResource()->get("Kendrick Lamar");
//echo $genius->getSearchResource()->getArtistId("Kendrick Lamar") . "\n";

//echo $genius->getArtistsResource()->get(1421) . "\n";
//echo $genius->getArtistsResource()->getArtistSocials(1421) . "\n";

//echo $genius->getSearchResource()->getArtistId(array("album" => "demain c'est loin", "artist" => "IAM"));
//echo $genius->getSearchResource()->getArtistId("IAM");

//echo $genius->getArtistsResource()->getArtistSocials($genius->getSearchResource()->getArtistId("IAM"));
//echo json_encode($genius->getArtistsResource()->getArtistSocials($genius->getSearchResource()->getArtistId(array("album" => "demain c'est loin", "artist" => "IAM")))); // OK

//echo json_encode($genius->getSongsResource()->getSong(378195)); // OK

//var_dump($genius->getSongsResource()->getSongLyrics(378195, true));
//var_dump($genius->getSongsResource()->getSongLyrics("https://genius.com/songs/378195", true));
//echo json_encode($genius->getSongsResource()->getSongLyrics("https://genius.com/Rap-francais-discographie-2019-lyrics"));

//echo $genius->getAnnotationsResource()->getFirstImage(9169440); // OK