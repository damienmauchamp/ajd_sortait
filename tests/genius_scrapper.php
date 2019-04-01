<?php

include dirname(__DIR__) . '/config.php';
ini_set("xdebug.var_display_max_children", -1);
ini_set("xdebug.var_display_max_data", -1);
ini_set("xdebug.var_display_max_depth", -1);
header("Content-type:text/html");

use Sunra\PhpSimple\HtmlDomParser;

define('MAX_FILE_SIZE', 60000000);

$year = 2013;

$r = '/(<(?:[^\s]+).*?data-id="(?<annotation_id>[^"]*?)".*?>)?(?:\*|\-) (?<day>(\d|X){2})\/(?<month>\d{2}) \: (?<artist>.*) (?:-|–) (?:<i>)?(?<album>.*)(?:\s*<\/i>\s*).*/';
$m = array();

$url = "https://genius.com/Rap-francais-discographie-$year-lyrics";

/*
echo json_encode(get($url, true));
exit;
$response = \Httpful\Request::get($url)->send();
$code = $response->code;
if ($code === 301) {
	$url = "https://genius.com/Rap-francais-discographie-$year-annotated";
	$response = \Httpful\Request::get($url)->send();
	$code = $response->code;
}
$dom = HtmlDomParser::str_get_html($response->raw_body);
*/
$html = cleanString(get($url, true));
$dom = HtmlDomParser::str_get_html($html);

$raw = ""; // for unknown

foreach ($dom->find('.lyrics') as $element) {
	preg_match_all($regex_with_id, $element->innertext, $m, PREG_SET_ORDER, 0);
	$raw .= htmlspecialchars_decode($element->plaintext);
}

//response->annotation->body->dom->(tag=p)children->(tag=p)children->(tag=img)attributes->src

// test
file_put_contents(__DIR__ . "/test.json", json_encode(array("matches" => getAlbumsMatches($m, $year), "raw" => $raw)));

echo json_encode(array("matches" => getAlbumsMatches($m, $year), "raw" => $raw));
exit;