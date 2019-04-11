<?php

namespace Bot\Post;

class Post {

	/** @var Album */
	protected $album;

	protected $artist_social;

	protected $artwork;

	protected $caption;

	protected $hashtags;

	public $content;

	protected $connection;

    public function __construct(\Bot\Album $album)
    {
    	$this->album = $album;
    	$this->artist_social = $this->getArtistSocial() ? "@" . $this->getArtistSocial() . " " : "";
    	$this->artwork = $album->getArtwork();

    	$this->caption = $this->getCaption();
    	$this->hashtags = $this->getHashtags();

    	$this->content = $this->caption . "\n\n" . $this->artist_social . $this->hashtags;

    	$this->connect();
    }

	// caption
	protected function getCaption() {
		$regexEP = "/\s+\(EP\)$/";

	    $name = $this->album->getName();
	    $year = $this->album->getYear();
	    $artist = $this->album->getArtist(true);

	    $old = date("Y") - $year;

	    $caption = "L'album ";
	    if (preg_match($regexEP, $name)) {
	        $caption = "L'EP ";
	        $name = preg_replace("/ +\(EP\)$/", "", $name);
	    } else if (preg_match("/compilation/", strtolower($name))) {
	        $caption = "La compilation ";
	    }
	    $caption .= "\"${name}\" ${artist}sortait il y a ${old} an" . ($old > 1 ? "s" : "") . ".";

	    return $caption;
	}

	protected function getHashtags() {
		return trim( self::getHashtag($this->album->getArtist(true, false)) . " " . self::getHashtag($this->album->getName(), true) );
	}

	public static function getHashtag($str, $is_album = false) {
		$replacements = (object) array(
			"search" => array(
				($is_album ? REGEX_ALBUM_BRACKETS : "//"),
				"/\\$/",
				"/\€/",
				REGEX_ONLY_ALPHANUMERIC
			),
			"replace" => array(
				"",
				"S",
				"E",
				""
			)
		);
		$h = preg_replace($replacements->search, $replacements->replace, $str);
		$h = array_map('ucfirst', explode(" ", strtolower($h)));
		$h = implode("", $h);
		return !is_numeric($h) ? "#" . $h : "";
	}
}