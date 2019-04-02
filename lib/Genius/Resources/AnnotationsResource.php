<?php

namespace Genius\Resources;

//use Genius\Authentication\Scope;

/**
 * Class AnnotationsResource
 * @package Genius\Resources
 *
 * @see https://docs.genius.com/#annotations-h2
 */
class AnnotationsResource extends AbstractResource
{
    
    public function get($id, $text_format = 'dom')
    {
        return $this->sendRequest('GET', 'annotations/' . $id . '/?', ['text_format' => $text_format]);
    }

    public function getFirstImage($id) {

    	$request = $this->get($id, 'dom');
    	if ($response = $this->success($request)) {

			$body = $response["annotation"]["body"];

			if ($dom = $body['dom']) {
				$prev = false;
				$results = [];
				array_walk_recursive($dom, function($value, $key) use (&$prev, &$res){
					if ($key === "tag" && $value === "img") {
						$prev = true;
					} else if ($prev && $key === "src") {
						$results[] = $value;
						return;
					}
				});
				return isset($results[0]) ? $results[0] : null;
			}
			
			return false;

    	} else {
	    	return false;
	    }
    }
}