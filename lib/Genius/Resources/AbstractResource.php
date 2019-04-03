<?php

namespace Genius\Resources;

use Genius\Genius;

class AbstractResource
{
    
    const API_URL = 'https://api.genius.com/';
    
    protected $genius;
    
    public function __construct(Genius $genius)
    {
        $this->genius = $genius;
    }

    protected function sendRequest($method, $uri, array $params = [], $raw_scraping = false) {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(

            CURLOPT_CUSTOMREQUEST => $method,        //set request type post or get
            CURLOPT_POST => false,        //set to GET
            CURLOPT_USERAGENT => $user_agent, //set user agent
            CURLOPT_COOKIEFILE => "cookie.txt", //set cookie file
            CURLOPT_COOKIEJAR => "cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING => "",       // handle all encodings
            CURLOPT_AUTOREFERER => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT => 120,      // timeout on response
            CURLOPT_MAXREDIRS => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        );

        $url = ($raw_scraping ? $uri : self::API_URL . $uri . http_build_query(array_merge(["access_token" => $this->genius->getAccessToken()], $params)));

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $raw_scraping ? trim($content) : strip_tags($content);
        return $header["content"];
    }

    protected function success($response) {

        if (!$response) {
            return false;
        }

        $result = json_decode($response, true);
        if ($result["meta"]["status"] !== 200) {
            return false;
        }

        return $result["response"];
    }
    
    /*protected function _sendRequest($method, $uri, array $headers = [], $body = null, $protocolVersion = '1.1') {
        $req =  $this->genius->getRequestFactory()->createRequest($method, self::API_URL . $uri, $headers, $body, $protocolVersion);
    
        return json_decode($this->genius->getHttpClient()->sendRequest($req)->getBody());
    }*/
    
}