<?php

namespace Genius\Resources;

/**
 * Class SongsResource
 * @package Genius\Resources
 *
 * @see https://docs.genius.com/#songs-h2
 */
class SongsResource extends AbstractResource
{
    
    public function get($id, $text_format = 'dom')
    {
        return $this->sendRequest('GET', 'songs/' . $id . '/?', ['text_format' => $text_format]);
    }

    public function getSong($id, $text_format = 'dom')
    {
    	$request = $this->get($id, $text_format);
    	if ($response = $this->success($request)) {
			return $response;
    	}
    	return false;
    }

    // q: int|string(url)
    public function getSongLyrics($q, $html = false)
    {
    	//https://genius.com/songs/378195
    	//sendRequest($method, $uri, array $params = [], $raw_scraping = false)

    	$url = intval($q) ? ("https://genius.com/songs/" . $q) : $q;
    	if ($response = $this->sendRequest('GET', $url, [], true)) {
    		return $html ? $response : strip_tags($response);
    	}
        return false;
    }
    
}