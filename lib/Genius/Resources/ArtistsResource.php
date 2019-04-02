<?php

namespace Genius\Resources;

/**
 * Class ArtistsResource
 * @package Genius\Resources
 *
 * @see https://docs.genius.com/#artists-h2
 */
class ArtistsResource extends AbstractResource
{
    
    public function get($id, $text_format = 'dom')
    {
        return $this->sendRequest('GET', 'artists/' . $id . '/?', ['text_format' => $text_format]);
    }
    
    public function getArtistSocials($id, $text_format = 'dom')
    {
    	$request = $this->get($id, $text_format);
    	if ($response = $this->success($request)) {
    		
			$artist = $response["artist"];
			$artist_socials = array(
				"facebook" => $artist["facebook_name"],
				"twitter" => $artist["twitter_name"],
				"instagram" => $artist["instagram_name"]
			);

    		return $artist_socials;
    	} else {
	    	return false;
	    }
    }
    
    /*public function getSongs($id, array $data)
    {
        return $this->sendRequest('GET', 'artists/' . $id . '/songs/?' . http_build_query($data));
    }*/
    
}