<?php


define('URL', 'http://webservices.amazon.com/onca/xml');

  // The URL to fetch is the hostname + path
foreach($_REQUEST as $k => $v){
    $cleaned[] = urlencode($k) . '=' . urlencode($v);
}
$url = URL . '?' . implode('&', $cleaned);

  // Open the Curl session
$session = curl_init($url);
// Don't return HTTP headers. Do return the contents of the call
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
// Make the call
  $xml = curl_exec($session);

// The web service returns XML
header("Content-Type: text/xml");

echo $xml;
curl_close($session);


?>