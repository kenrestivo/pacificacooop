<?php
 
// if no text is provided, we will use some default text
if (!isset($_GET["text"])){
    $text = "No text supplied. There is a bug somewhere!!";
}    else {
    $text = $_GET["text"];
}


if (!isset($_GET["size"])){
    $font_size = 18;
}    else {
    $font_size = $_GET["size"];
}

/// XXX HACK! get from database instead!!
if (!isset($_GET["font"])){
    $font = 'Bernhard_Modern_BT.ttf';
}    else {
    $font = $_GET["font"];
}

/// XXX HACK! use ABSOLUTE URL here instead!
$fontpath = "../../fonts/$font";


// angle of the font in degrees
$font_angle = 0;
// the weight of the font stroke
$stroke = 2;
// the position of the font
 


$bbox = imagettfbbox($font_size, $font_angle, $fontpath, $text);
$x_size = abs($bbox[4] - $bbox[0]);
$y_size = abs($bbox[5] - $bbox[1]);

// gah, descenders. multiply by 1.25 to guess
$im = imagecreate($x_size * 1.05, $y_size * 1.25);

 
// define the colours that we will be using
$bgcolor = imagecolorallocate($im, 255, 255, 255);
$textcolor = imagecolorallocate($im, 0, 0, 0);
 
$startx = $font_size/4;
$starty = $font_size*1.25;

imagettftext($im, $font_size, $font_angle, $startx, $starty, 
             $textcolor, $fontpath, $text);
 
// set the correct HTTP header for a PNG image
header("Content-type: image/png");
 
imagepng($im);
 
// remember to free up the memory used on the server to create the image!
imagedestroy($im);

?>
