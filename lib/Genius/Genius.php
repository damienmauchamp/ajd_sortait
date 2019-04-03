<?php

namespace Genius;

use Genius\Resources;
/*use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\Authentication;
use Http\Message\MessageFactory;*/

/**
 * Class Genius
 * @package Genius
 *
 * @//method Resources\AccountResource getAccountResource()
 * @method Resources\AnnotationsResource getAnnotationsResource()
 * @method Resources\ArtistsResource getArtistsResource()
 * @//method Resources\ReferentsResource getReferentsResource()
 * @method Resources\SearchResource getSearchResource()
 * @method Resources\SongsResource getSongsResource()
 * @//method Resources\WebPagesResource getWebPagesResource()
 */

class Genius
{

    /** @var string */
	protected $access_token;

	public function __construct($access_token) {
		$this->access_token = $access_token;
	}

	public function getAccessToken() {
		return $this->access_token;
	}

	public function __call($name, $arguments)
	{

	    if (strpos($name, 'get') !== 0) {
	        return false;
	    }
	    
	    $name = '\\Genius\\Resources\\' . substr($name, 3);
	    if (!class_exists($name)) {
	        return false;
	    }
	    
	    if (isset($this->resourceObjects[ $name ])) {
	        return $this->resourceObjects[ $name ];
	    }
	    
	    $this->resourceObjects[ $name ] = new $name($this);
	    
	    return $this->resourceObjects[ $name ];
	}
}