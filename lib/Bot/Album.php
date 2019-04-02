<?php

namespace Bot;

/*
artist
album
day
month
année
id_annot
posted { ig, twitter }
heure post
artwork

*/

/**
 * Class Album
 * @package Bot
 *
 */

class Album
{
	private $artist;

	private $artist_socials;

	private $name;

	private $type = "album";

	private $release_date;

	private $posted;

	private $post_date;

	private $artwork;

	private $annotation_id;

	public function __construct(array $item) {
		$this->artist = $item["artist"];
		$this->name = $item["album"];
		//$this->type = $item["type"];
		$this->release_date = new \DateTime("${item['year']}-${item['month']}-${item['day']}");
		$this->posted = $item["posted"];
		$this->post_date = $item["post_date"];
		$this->artwork =  dirname(dirname(__DIR__)) . $item["artwork"];
		$this->annotation_id = $item["annotation_id"];

		$this->findArtistSocials();
	}

	public function findArtistSocials() {
		$genius = new \Genius\Genius($_ENV['GENIUS_CLIENT_ACCESS_TOKEN']);
		$this->artist_socials = $genius->getArtistsResource()->getArtistSocials($genius->getSearchResource()->getArtistId(array("album" => strtolower($this->name), "artist" => $this->artist)));
	}

	public function getName($caption = false) {
		return str_replace("&amp;", "&", $this->name);
	}

	public function getArtist($caption = false, $accord = true) {
		if ($caption && !in_array($this->artist, array("Artistes multiples", "Various Artists", "Multi-interprètes"))) {
			$artist = $this->artist;
			if ($artist !== '' && $accord) {
		        if (startsWithVowel($artist)) {
		            return "d'${artist} ";
		        } else if (startsWith(strtolower($artist), "les")) {
		            $artist = preg_replace("/^(l|L)es /", "", $artist);
		            return "des ${artist} " ;
		        } else if (startsWith(strtolower($artist), "le")) {
		            $artist = preg_replace("/^(l|L)e /", "", $artist);
		            return "du ${artist} " ;
		        } else {
		            return "de ${artist} ";
		        }
		    } else if (!$accord) {
		    	return $this->artist;
		    }
			return "";
		}
		return $this->artist;
	}

	public function getDate() {
		return $this->release_date;
	}

	public function getYear() {
		return $this->release_date->format("Y");
	}

	public function getArtwork() {
		return $this->artwork;
	}

	// socials

	public function getArtistSocials() {
		return $this->artist_socials;
	}

	public function getArtistSocial($social) {
		return $social && $this->artist_socials && isset($this->artist_socials[$social]) ? $this->artist_socials[$social] : false;
	}

	public function getArtistFacebook() {
		return $this->getArtistSocial("facebook");
	}

	public function getArtistInstagram() {
		return $this->getArtistSocial("instagram");
	}

	public function getArtistTwitter() {
		return $this->getArtistSocial("twitter");
	}
}