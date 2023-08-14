<?php

namespace Bot\Post;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;

class TwitterPost extends Post {

	/**
	 * @var TwitterOAuth
	 */
	protected $connection;

	protected function getArtistSocial() {
		return $this->album->getArtistSocial("twitter");
	}

	protected function connect() {
		$this->connection = new TwitterOAuth($_ENV["TWITTER_API_KEY"], $_ENV["TWITTER_API_SECRET_KEY"], $_ENV["TWITTER_ACCESS_TOKEN"], $_ENV["TWITTER_ACCESS_TOKEN_SECRET"]);
		$this->connection->setTimeouts(60, 30);
		return true;
	}

	public function post($prod, $debug = false): array {

		if(isset($_ENV['POST_ON_TWITTER']) && !boolval($_ENV['POST_ON_TWITTER'])) {
			$this->log(array(
				'debug' => $debug,
				'media' => $this->artwork,
				'not_posted' => true
			));
			return ['posted' => false, 'error' => false, 'message' => 'Posts on Twitter disabled'];
		}

		try {

			// API v1.1 for media
			$this->connection->setApiVersion('1.1');
			$media = $this->connection->upload('media/upload', ['media' => $this->artwork]);
			$media_id_string = $media->media_id_string ?? false;

			if(!$media_id_string) {
				$this->log(array(
					'debug' => $debug,
					'media' => $this->artwork,
					'not_posted' => true,
					'error' => $media->errors ?? null
				));
				return ['posted' => false, 'error' => "Error while uploading media ({$this->connection->getLastHttpCode()})", 'message' => 'Error while uploading media'];
			}

			$parameters = [
				'text' => $this->content,
				'media' => ['media_ids' => [$media_id_string]],
			];

			$this->log([
				'debug' => $debug,
				'media' => $this->artwork,
				'parameters' => $parameters
			]);

			if($prod) {
				// API v2 for posting
				$this->connection->setApiVersion('2');
				$posting = $this->connection->post('tweets', $parameters, true);

				if($posting->errors ?? false) {
					return ['posted' => false, 'error' => $posting->errors, 'message' => 'Error while posting'];
				}
				return ['posted' => true, 'error' => false, 'message' => ''];
			}
		} catch (TwitterOAuthException $exception) {
			return ['posted' => false, 'error' => "Exception: {$exception->getMessage()}", 'message' => 'Error while posting'];
		}
		return ['posted' => false, 'error' => false, 'message' => 'Twitter post simulated'];
	}

	public function log($data = []) {
		return $this->logging('twitter', $data);
	}

}
