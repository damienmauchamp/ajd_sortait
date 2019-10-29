<?php

namespace Bot\Post;

use \InstagramAPI\Instagram;

Class InstagramPost extends Post {

	protected function getArtistSocial() {
		return $this->album->getArtistSocial("instagram");
	}

	protected function connect() {
		$this->connection = new Instagram();
	    try { // connexion
	    	$this->connection->login($_ENV["INSTAGRAM_USERNAME"], $_ENV["INSTAGRAM_PASSWD"]);
	    } catch (\Exception $e) {
	    	echo 'Something went wrong (1): ' . $e->getMessage() . "\n";
	    	exit(0);
	    }
	    return true;
	}

	public function post() {

		$userId = "";

		$metadata = array(
			'caption' => $this->content,
			//'usertags' => [['position' => [0.5, 0.5], 'user_id' => $userId]]
		);

    	try { // publication
    		$photo = new \InstagramAPI\Media\Photo\InstagramPhoto($this->artwork, ['targetFeed' => \InstagramAPI\Constants::FEED_TIMELINE]);
        	$this->log(array(
        		'step' => 1,
        		'artwork' => $this->artwork,
        		'metadata' => $metadata
        	));

    		//$media = $this->connection->timeline->uploadPhoto($photo->getFile(), ['caption' => $this->content]);
        	$this->log(array(
        		'step' => 2,
        		'artwork' => $this->artwork,
        		'metadata' => $metadata
        	));
        	$this->log(array(
        		'connection' => $this->connection
        	));

    		$media = $this->connection->timeline->uploadPhoto($photo->getFile(), $metadata);

        	$this->log(array(
        		'step' => 3,
        		'artwork' => $this->artwork,
        		'metadata' => $metadata
        	));
    	} catch (\Exception $e) {
    		echo 'Something went wrong (2): ' . $e->getMessage() . "\n";
    	}

    	return $media ? $media : false;
    }

    public function log($data = []) {
        return $this->logging('instagram', $data);
    }
}