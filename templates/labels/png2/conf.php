<?php

$imgname = "$kret_szablon/label.png";
$img = imagecreatefrompng($imgname); /* Attempt to open */

$czarny = imagecolorallocate($img, 0, 0, 0);
$czerwony = imagecolorallocate($img, 240, 0, 0);
//$font = "../../fonts/cyberbit.ttf";
//$font = "../fonts/techniczna.ttf";
$font = '../fonts/lucida.ttf';

//imagealphablending($img, false);
imagesavealpha($img, true);

imagettftext($img, 17, 0, 61, 143, $czarny, $font, "$kret_nazwa");
imagettftext($img, 17, 0, 373, 143, $czarny, $font, $kret_owner);
imagettftext($img, 13, 0, 61, 291, $czarny, $font, wordwrap(stripcslashes(strip_tags($kret_opis, '<img>')), 66));

imagettftext($img, 17, 0, 61, 221, $czarny, $font, $kret_id);
imagettftext($img, 17, 0, 379, 221, $czarny, $font, $kret_tracking);

header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
