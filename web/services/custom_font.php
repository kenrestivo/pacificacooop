<?php
 
// if no text is provided, we will use some default text
if (!isset($_GET["section_heading"]))
    $text = "Please Enter Some Text!!";
else
    $text = $_GET["section_heading"];
 
// set this to the location of the TrueType font file that you want to use
$font = "/mnt/kens/ki/proj/coop/imports/Bernhard_Modern_BT.ttf";
$font_size = 18;
// angle of the font in degrees
$font_angle = 0;
// the weight of the font stroke
$stroke = 2;
// the position of the font
$startx = 10;
$starty = 30;
 
$im = imagecreate(450,40);
 
// define the colours that we will be using
$white = imagecolorallocate($im, 255, 255, 255);
$blue1 = imagecolorallocate($im, 175, 188, 199);
$blue2 = imagecolorallocate($im, 64, 99, 122);
$black = imagecolorallocate($im, 0, 0, 10);
 
//imagefilltoborder($im, 0, 0, $blue1, $blue1);

imagettftext($im, $font_size, $font_angle, $startx, $starty, $black, $font, $text);
 
// set the correct HTTP header for a PNG image
header("Content-type: image/png");
 
imagepng($im);
 
// remember to free up the memory used on the server to create the image!
imagedestroy($im);

?>
