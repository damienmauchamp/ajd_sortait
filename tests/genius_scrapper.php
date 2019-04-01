<?php

include dirname(__DIR__) . '/config.php';

use Sunra\PhpSimple\HtmlDomParser;

define('MAX_FILE_SIZE', 6000000);

$r = '/(<(?:[^\s]+).*?data-id="(?<id>[^"]*?)".*?>)?(?:\*|\-) (?<day>(\d|X){2})\/(?<month>\d{2}) \: (?<artist>.*) (?:-|–) (?:<i>)?(?<album>.*)(?:\s*<\/i>\s*).*/';
$r_dom = '/(?:<(?:a).*?data-id="(?<id>[^"]*?)".*?>)?(?:\*|\-) (?<day>(?:\d|X){2})\/(?<month>\d{2}) \: (?<artist>(?:(?!\s-\s).)*)(?:\s(?:-|–)\s)(?:<i>)?(?<album>(?:(?!<\/?(i|a|br)>).)*)(?:\s*<\/i>\s*)?(?:<\/a>)?/';
$m = array();
$url = "https://genius.com/Rap-francais-discographie-2019-lyrics";
$response = \Httpful\Request::get($url)->send();
$code = $response->code;
$dom = HtmlDomParser::str_get_html($response->raw_body);

$raw = ""; // for unknown

foreach($dom->find('.lyrics') as $element) {
	preg_match_all($r_dom, $element->innertext, $m, PREG_SET_ORDER, 0);
	$raw .= htmlspecialchars_decode($element->plaintext);
}

echo json_encode(array("matches" => $m, "raw" => $raw));
exit;