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

	private $type;// = "album";

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
		$artist_socials = $genius->getArtistsResource()->getArtistSocials($genius->getSearchResource()->getArtistId(array("album" => strtolower($this->name), "artist" => $this->artist)));

		//$this->name
		if ($artist_socials !== null) {
			// try to find the artist in the file
			$search_id = $this->getArtistSocialsFromFile(true);
			if ($search_id === null) {
				// add
			} else {
				// edit
				$json = file_get_contents(DIR_DATA . "socials.json");
				$data = json_decode($json, true);
				//$data[$search_id]
			}
			$this->artist_socials = $artist_socials;
		} else  {
			// search in file
			$this->artist_socials = $this->getArtistSocialsFromFile();
		}
	}

	private function getArtistSocialsFromFile($return_id = false) {

		if (!is_file(DIR_DATA . "/socials.json")) {
			writeJSONFile("socials", []);
			return null;
		}

		// checking if the file exists
		// getting the content of it
		$json = file_get_contents(DIR_DATA . "socials.json");
		$data = json_decode($json, true);

		//
		$res = null;
		foreach ($data as $id => $artist) {

			// by artist name
			if ($artist['name'] == $this->artist) {
				$res = array(
					'source' => 'name',
					'id' => $id,
					'artist' => $artist
				);
				break;
			}

			// by genius name
			else if ($artist['genius'] !== null && $artist['genius']['artistName'] == $this->artist) {
				$res = array(
					'source' => 'genius',
					'id' => $id,
					'artist' => $artist
				);
				break;
			}

			// by itunes name
			else if ($artist['itunes'] !== null && $artist['itunes']['artistName'] == $this->artist) {
				$res = array(
					'source' => 'itunes',
					'id' => $id,
					'artist' => $artist
				);
				break;
			}
		}

		if ($res !== null) {
			// returning the id
			if ($return_id) {
				return $res['id'];
			}

			// ['band']['members'] : is a band, fetch members socials
			// ['band']['part_of'] : is part of a band, search band socials
			return array(
				"facebook" => null,
				"twitter" => $artist['twitter'] !== null ? $artist['twitter']['username'] : null,
				"instagram" => $artist['instagram'] !== null ? $artist['instagram']['username'] : null
			);
		}
		return null;
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