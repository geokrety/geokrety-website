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

imagettftext($img, 15, 0, 24, 149, $czarny, $font, "$kret_nazwa by $kret_owner");
imagettftext($img, 10, 0, 24, 170, $czarny, $font, wordwrap(stripcslashes(strip_tags($kret_opis, '<img>')), 50));

imagettftext($img, 15, 0, 214, 110, $czarny, $font, $kret_tracking);
imagettftext($img, 15, 0, 24, 110, $czarny, $font, $kret_id);

header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
