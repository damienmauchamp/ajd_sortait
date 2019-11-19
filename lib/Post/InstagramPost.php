<?php

namespace Bot\Post;

use \InstagramAPI\Instagram;

Class InstagramPost extends Post {

	protected function getArtistSocial() {
		return $this->album->getArtistSocial("instagram");
	}

	protected function connect() {
		$this->connection = new Instagram($_ENV['ENVIRONMENT'] === 'dev' || (isset($_ENV['DEBUG']) && boolval($_ENV['DEBUG'])));
	    try { // connexion
	    	$this->connection->login($_ENV["INSTAGRAM_USERNAME"], $_ENV["INSTAGRAM_PASSWD"]);
	    } catch (\Exception $e) {
	    	echo 'Something went wrong (1): ' . $e->getMessage() . "\n";
	    	exit(0);
	    }
	    return true;
	}

	public function post($prod, $debug = false) {

        $usertags = [];
        $n_tags = 0;
        $pos_Y = 0.8;

        $ig = new Instagram();
        try { // need to be logged
            $ig->login($_ENV["INSTAGRAM_USERNAME"], $_ENV["INSTAGRAM_PASSWD"]);
        } catch (\Exception $e) {
            echo 'Impossible de se connecter à Instagram: ' . $e->getMessage() . "\n";
        }

        // getting usertags
        $tags = $this->getArtistSocial();

        // setting usertags
        if ($tags && $ig) {
            $n_tags = count($tags);
            foreach ($tags as $i => $tag) {
                $this->log(["Searching for @{$tag}'s Instagram ID"]);
                try {
                    $id = $ig->people->getUserIdForName($tag);
                    $this->log(["Found Instagram ID '{$id}' for @{$tag}"]);
                    $pos_X = 1 / ($n_tags + 1)  * ($i + 1);
                    $usertags[] = ['position'=>[$pos_X, $pos_Y], 'user_id' => $id];
                } catch (\Exception $e) {
                    $id = '';
                    $this->log(["No Instagram ID found for for @{$tag}"]);
                }

            }
        }

		$metadata = array(
			'caption' => $this->content,
            'usertags' => ['in' => $usertags]
            /*'usertags' => [
                'in' => [
                    ['position'=>[0.3333333333333333, 0.8], 'user_id' => '1720416472'],
                    ['position'=>[0.6666666666666666, 0.8], 'user_id' => '225382963']
                ]
            ]*/
		);

        $this->log(array(
            'debug' => $debug,
            'artwork' => $this->artwork,
            'metadata' => $metadata
        ));
        
        $media = null;
        
        if (!$prod) {

        	try { // publication
        		$photo = new \InstagramAPI\Media\Photo\InstagramPhoto($this->artwork, ['targetFeed' => \InstagramAPI\Constants::FEED_TIMELINE]);

        		$media = $this->connection->timeline->uploadPhoto($photo->getFile(), $metadata);

        	} catch (\Exception $e) {
        		echo 'Something went wrong (2): ' . $e->getMessage() . "\n";
                return false;
        	}
        } else {
            return true;
        }

    	return $media && $media !== null ? $media : false;
    }

    public function log($data = []) {
        return $this->logging('instagram', $data);
    }
}