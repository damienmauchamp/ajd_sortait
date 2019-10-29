<?php

namespace Bot\Post;

use Abraham\TwitterOAuth\TwitterOAuth;

 Class TwitterPost extends Post {
 	
    protected function getArtistSocial() {
    	return $this->album->getArtistSocial("twitter");
    }

    protected function connect() {
    	$this->connection = new TwitterOAuth($_ENV["TWITTER_API_KEY"], $_ENV["TWITTER_API_SECRET_KEY"], $_ENV["TWITTER_ACCESS_TOKEN"], $_ENV["TWITTER_ACCESS_TOKEN_SECRET"]);
    	$this->connection->setTimeouts(60, 30);
        return true;
    }

    public function post($debug = false) {
    	$media = $this->connection->upload('media/upload', array('media' => $this->artwork));
	    $parameters = [
	        'status' => $this->content,
	        'media_ids' => implode(',', [$media->media_id_string])
	    ];

        $this->log(array(
            'debug' => $debug,
            'media' => $this->artwork,
            'parameters' => $parameters
        ));

        if (!$debug) {
    	   return $this->connection->post('statuses/update', $parameters);
        }
        return false;
    }

    public function log($data = []) {
        return $this->logging('twitter', $data);
    }

 }