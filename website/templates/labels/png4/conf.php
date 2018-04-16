<?php

$imgname = "$kret_szablon/label.png";
$img = imagecreatefrompng($imgname); /* Attempt to open */

$czarny = imagecolorallocate($img, 0, 0, 0);
$bialy = imagecolorallocate($img, 255, 255, 255);
$czerwony = imagecolorallocate($img, 240, 0, 0);
$font = '../fonts/DejaVuSans.ttf';
$font2 = '../fonts/DejaVuSansCondensed.ttf';

//imagealphablending($img, false);
imagesavealpha($img, true);

imagettftext($img, 19, 0, 270, 42, $czarny, $font, "$kret_nazwa");

imagettftext($img, 19, 0, 270, 120, $czarny, $font, "$kret_owner");

imagettftext($img, 19, 0, 270, 195, $czarny, $font, $kret_id);
imagettftext($img, 19, 0, 270, 272, $bialy, $font, $kret_tracking);

imagettftext($img, 21, 0, 500, 42, $czarny, $font, "$kret_nazwa");
imagettftext($img, 14, 0, 500, 72, $czarny, $font2, wordwrap(stripcslashes(strip_tags($kret_opis, '<img>')), 50));

header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
