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
    	$this->artist_social = $this->artistSocialsToString($this->getArtistSocial());
    	$this->artwork = $album->getArtwork();

    	$this->caption = $this->getCaption();
    	$this->hashtags = $this->getHashtags();

    	$this->content = $this->caption . "\n\n" . $this->artist_social . $this->hashtags;

    	$this->connect();
    }

    // socials to string
    protected function artistSocialsToString($artist_socials) {
    	if (!$artist_socials) {
    		return '';
    	}

    	if (is_array($artist_socials)) {
    		$socials = '';
    		foreach ($artist_socials as $social) {
    			$socials .= ($social && $social !== null ? "@$social " : "");
    		}
    		return $socials;
    	}
		return $artist_socials && $artist_socials !== null ? "@" . $artist_socials . " " : "";
    }

	// caption
	protected function getCaption() {
		$regexEP = "/\s+\(EP\)$/";

	    $name = $this->album->getName();
	    $year = $this->album->getYear();
	    $artist = trim($this->album->getArtist(true));
	    $old = date("Y") - $year;

//	    $caption = "L'album ";
//	    if (preg_match($regexEP, $name)) {
//	        $caption = "L'EP ";
//	        $name = preg_replace("/ +\(EP\)$/", "", $name);
//	    } else if (preg_match("/compilation/", strtolower($name))) {
//	        $caption = "La compilation ";
//	    } else {
//
//	    }
//	    $caption .= "\"${name}\" ${artist} sortait il y a ${old} an" . ($old > 1 ? "s" : "") . ".";
//		return $caption;
		// v2
		$captions = [
			'"{{album}}" de {{artist}} sortait il y a {{years}} {{suffixe}}.',
			'"{{album}}" de {{artist}} fête ses {{years}} {{suffixe}}.',
			'"{{album}}" de {{artist}} fête ses {{years}} {{suffixe}} aujourd\'hui.',
			'"{{album}}" de {{artist}} fête aujourd\'hui ses {{years}} {{suffixe}}.',
		];
		$captions_singulier = [
			'{{artist}} sortait son projet "{{album}}" il y a {{years}} {{suffixe}}.',
			'{{artist}} nous révélait son projet "{{album}}" il y a {{years}} {{suffixe}}.',
			'{{artist}} révélait "{{album}}" il y a {{years}} {{suffixe}}.',
		];
		$captions_celebration = [
		];
		$captions_compilations = [
			'Le projet "{{album}}" fête ses {{years}} {{suffixe}}.',
			'Le projet "{{album}}" a {{years}} {{suffixe}} aujourd\'hui.',
			'Le projet "{{album}}" fête ses {{years}} {{suffixe}} aujourd\'hui.',
		];
		$captions_type = [
			'{{type}}"{{album}}" de {{artist}} fête ses {{years}} {{suffixe}} aujourd\'hui.',
			'{{type}}"{{album}}" de {{artist}} fête ses {{years}} {{suffixe}}.',
			'{{artist}} sortait {{type_m}} "{{album}}" il y a {{years}} {{suffixe}}.',
			'{{artist}} sortait {{type_m}} "{{album}}" il y a {{years}} {{suffixe}} aujourd\'hui.',
		];

		// Type
		$type = '';
		if(preg_match($regexEP, $name)) {
			$type = "L'EP ";
			$name = preg_replace("/ +\(EP\)$/", "", $name);
			$captions = array_merge($captions, $captions_type);
		}
		else if(preg_match("/compilation/", strtolower($name))) {
			$type = "La compilation ";
			$captions = array_merge($captions, $captions_type, $captions_compilations);
		}

		// not plural artists
		if(!strstr($artist, "artistes multiples") &&
			!strstr($artist, "multi-interprètes") &&
			!strstr($artist, "various artists") &&
			!strstr($artist, " les ") &&
			!strstr($artist, "&")) {
			$captions = array_merge($captions, $captions_singulier);
		} else if(strstr(mb_strtolower($artist), "artistes multiples") &&
			!strstr(mb_strtolower($artist), "multi-interprètes") &&
			!strstr(mb_strtolower($artist), "various artists")) {
			foreach ($captions as &$caption) {
				$caption = str_replace(" de {{artist}}", "", $caption);
			}
		}

		$suffixe = 'an'.($old > 1 ? 's' : '');

		return trim(str_replace(
			['{{years}}', '{{artist}}', '{{album}}', '{{type}}', '{{type_m}}', '{{suffixe}}'],
			[$old, $artist, $name, $type, mb_strtolower($type), $suffixe],
			$captions[rand(0, count($captions) - 1)]));
	}

	protected function getHashtags() {
		$artistHashtag = self::getHashtag($this->album->getArtist(true, false));
		$albumHashtag = self::getHashtag($this->album->getName(), true);
		return  trim(($artistHashtag !== '#' ? $artistHashtag : '').($albumHashtag !== '#' ? $albumHashtag : ''));
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

	protected function logging($type, $data = []) {
		if (in_array($type, ['instagram', 'twitter'])) {
			require_once __DIR__ . '/../../vendor/autoload.php';
			$logger = new \Monolog\Logger($type);
			$logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../../logs/' . $type . '/' . $type . '_' . date('Ymd') . '.log', \Monolog\Logger::INFO));

	        $logger->info("post --> '" . $this->album->getAlbumName() . "' by " . $this->album->getArtistName(), $data ? $data : array(
	            'artwork' => $this->artwork,
	            'content' => $this->caption . "\n\n" . $this->artist_social . $this->hashtags
	        ));
		}
        return true;
	}
}