<?php

$imgname = "$kret_szablon/label.jpg";
$img = imagecreatefromjpeg($imgname); /* Attempt to open */

$czarny = imagecolorallocate($img, 0, 0, 0);
$czerwony = imagecolorallocate($img, 240, 0, 0);
//$font = "../../fonts/cyberbit.ttf";
//$font = "../fonts/techniczna.ttf";
$font = '../fonts/lucida.ttf';

imagettftext($img, 15, 0, 246, 44, $czarny, $font, "$kret_nazwa");
imagettftext($img, 15, 0, 252, 122, $czarny, $font, $kret_owner);
imagettftext($img, 10, 0, 252, 212, $czarny, $font, wordwrap(stripcslashes(strip_tags($kret_opis, '<img>')), 30));

imagettftext($img, 15, 0, 572, 44, $czarny, $font, $kret_tracking);
imagettftext($img, 15, 0, 792, 44, $czarny, $font, $kret_id);

header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);
