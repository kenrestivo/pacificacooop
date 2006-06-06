<?php

chdir('../'); // for test folder

require_once('CoopPage.php');
require_once 'Services/JSON.php';


$cp = new CoopPage($debug);
$cp->pageTop(); /// HAVE TO DO THIS TO FISH OUT THE AUTH STUFF FROM SESSION!
                /// but don't actually PRINT it, it'll fuck up the return value

 
$input = $GLOBALS['HTTP_RAW_POST_DATA'];

$json = new Services_JSON();
$value = $json->decode($input);
$cp->confessArray($value, 'json value', 1);

// echo it back, in json format, as it was sent
print $input;

$cp->flushBuffer();

?>