<?php

namespace Bot;

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
		$genius_artist_id = $genius->getSearchResource()->getArtistId(array("album" => strtolower($this->name), "artist" => $this->artist));
		$artist_socials = $genius->getArtistsResource()->getArtistSocials($genius_artist_id);

		if ($artist_socials !== null) {

			// try to find the artist in the file
			$search_id = $this->getArtistSocialsFromFile(true);

			$json = file_get_contents(DIR_DATA . "socials.json");
			$data = json_decode($json, true);

			// creating the artist
			if ($search_id === null) {

				// retrieving the max id
				$id = intval(array_reduce($data, function ($a, $b) {
					return @$a['id'] > $b['id'] ? $a : $b;
				})['id']) +1;

				echo logsTime() . "[SOCIALS] " . $this->artist . " adding...\n";

				// creating the instance
				$data[$id] = array(
					'id' => $id,
					'name' => $this->artist,
					'genius' => array(
						'id' => $genius_artist_id,
						'artistName' => $this->artist
					),
					'itunes' => null,
					'instagram' => null,
					'twitter' => null,
					'band' => null,
					'updates' => array(
						'auto' => new \DateTime(),
						'manually' => false
					)
				);

				//
				if ($artist_socials['twitter']) {
					$data[$id]['twitter'] = array(
						'id' => null,
						'username' => $artist_socials['twitter']
					);
				}

				//
				if ($artist_socials['instagram']) {
					$data[$id]['instagram'] = array(
						'id' => null,
						'username' => $artist_socials['instagram']
					);
				}

				// LOGS
				echo logsTime() . "[SOCIALS] " . $this->artist . " added : " . json_encode($data[$id]) . "\n";

			}
			// editing the artist
			else {
				$old_twitter = $data[$search_id]['twitter'];
				$old_instagram = $data[$search_id]['instagram'];

				echo logsTime() . "[SOCIALS] " . $this->artist . " editing... : " . json_encode($data[$search_id]) . "\n";

				if ($artist_socials['twitter']) {
					$new_twitter = array(
						'id' => $old_twitter !== null ? $old_twitter['id'] : null,
						'username' => $artist_socials['twitter']
					);
					$data[$search_id]['twitter'] = $new_twitter;
					echo logsTime() . "[SOCIALS] " . $this->artist . " twitter edited (" . json_encode($old_twitter) . " => " . json_encode($new_twitter) . ")\n";
				}

				if ($artist_socials['instagram']) {
					$new_instagram = array(
						'id' => $old_instagram !== null ? $old_instagram['id'] : null,
						'username' => $artist_socials['instagram']
					);
					$data[$search_id]['instagram'] = $new_instagram;
					echo logsTime() . "[SOCIALS] " . $this->artist . " twitter edited (" . json_encode($old_instagram) . " => " . json_encode($new_instagram) . ")\n";
				}

				// LOGS
				echo logsTime() . "[SOCIALS] " . $this->artist . " edited : " . json_encode($data[$search_id]) . "\n";
			}
			writeJSONFile("socials", $data);
		}

		// searching socials in file
		$this->artist_socials = $this->getArtistSocialsFromFile();
	}

	private function getArtistSocialsFromFile($return_id = false) {

		$return = array(
			"facebook" => [],
			"twitter" => [],
			"instagram" => []
		);

		if (!is_file(DIR_DATA . "socials.json")) {
			writeJSONFile("socials", []);
			return $return;
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

			// artist socials
			if ($res['artist']['twitter'] !== null && $res['artist']['twitter']['username'] !== null) {
				$return['twitter'][] = $res['artist']['twitter']['username'];
			}
			if ($res['artist']['instagram'] !== null && $res['artist']['instagram']['username'] !== null) {
				$return['instagram'][] = $res['artist']['instagram']['username'];
			}

			// ['band']['members'] : is a band, fetch members socials
			if ($res['artist']['band']) {

				//
				echo logsTime() . "[SOCIALS] Band found for '" . $this->artist . "'\n";
				if (!empty($res['artist']['band']['members'])) {
					echo logsTime() . "[SOCIALS] Band members found for '" . $this->artist . "'\n";
					foreach ($res['artist']['band']['members'] as $member_id) {

						$member_key = array_search($member_id, array_column($data, 'id'));
						$member_key_id = array_keys($data)[$member_key];
						$member = $data[$member_key_id];
						echo logsTime() . "[SOCIALS] Band member: " . $member['name'] . " ($member_id)\n";
						if ($member['twitter'] !== null && $member['twitter']['username'] !== null) {
							$return['twitter'][] = $member['twitter']['username'];
						}
						if ($member['instagram'] !== null && $member['instagram']['username'] !== null) {
							$return['instagram'][] = $member['instagram']['username'];
						}
					}
				}
			}
			// ['band']['part_of'] : is part of a band, search band socials
			return $return;
		}

		return $return_id ? null : $return;
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

	public function getAlbumName() {
		return $this->artist;
	}

	public function getArtistName() {
		return $this->artist;
	}
}