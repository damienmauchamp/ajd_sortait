<?php

namespace Genius\Resources;

/**
 * Class ArtistsResource
 * @package Genius\Resources
 *
 * @see https://docs.genius.com/#search-h2
 */
class SearchResource extends AbstractResource
{
    
    public function get($query)
    {
        return $this->sendRequest('GET', 'search/?', ['q' => $query]);
    }
    
    public function getArtistId($query)
    {

    	if (is_array($query)) {
            $q = clearAlbum(implode(" ", $query)); // @todo : bricolage, prendre liste des possibilités
    		$artist_name = $query["artist"];
    	} else {
    		$artist_name = $q = $query;
    	}

    	$request = $this->get($q);

    	if ($response = $this->success($request)) {

    		$artist_id = false;

        	// aucun résultat correspondant
			if (!count($response["hits"])) {
				return false;
			}

			// exact match
			/*foreach ($response["hits"] as $hit) {
				$result = $hit["result"];
				if (preg_match("/^" . strtolower($artist_name) . "$/", strtolower($result["primary_artist"]["name"]))) {
					return $result["primary_artist"]["id"];
				}
			}*/

			foreach ($response["hits"] as $hit) {
				$result = $hit["result"];
				if (preg_match("/" . strtolower($artist_name) . "/", strtolower($result["primary_artist"]["name"]))) {
					return $result["primary_artist"]["id"];
				}
			}

			return false;
    	}

    	return false;
    }
    
}