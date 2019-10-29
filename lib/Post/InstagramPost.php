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
    		//$media = $this->connection->timeline->uploadPhoto($photo->getFile(), ['caption' => $this->content]);
    		$media = $this->connection->timeline->uploadPhoto($photo->getFile(), $metadata);

        	$this->log(array(
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