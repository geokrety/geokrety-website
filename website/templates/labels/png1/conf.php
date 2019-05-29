<?php

$imgname = "$kret_szablon/geokret_label_v1.png";
$img = imagecreatefrompng($imgname); /* Attempt to open */

$czarny = imagecolorallocate($img, 0, 0, 0);
$czerwony = imagecolorallocate($img, 240, 0, 0);
//$font = "../../fonts/cyberbit.ttf";
//$font = "../fonts/techniczna.ttf";
$font = '../fonts/lucida.ttf';

imagettftext($img, 35, 0, 600, 100, $czarny, $font, "$kret_nazwa");
imagettftext($img, 35, 0, 600, 287, $czarny, $font, $kret_owner);
imagettftext($img, 25, 0, 600, 500, $czarny, $font, wordwrap(stripcslashes(strip_tags($kret_opis, '<img>')), 35));

imagettftext($img, 35, 0, 1420, 100, $czarny, $font, $kret_tracking);
imagettftext($img, 35, 0, 2010, 100, $czarny, $font, $kret_id);

header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);
