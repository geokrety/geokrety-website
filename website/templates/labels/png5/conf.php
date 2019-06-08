<?php

/*
 * Geokrety label template
 *
 * Template name: Modern :: Wallson 2 (with QR)
 */

$imgname = "$kret_szablon/geokret_label_v2.png";
$img = imagecreatefrompng($imgname);

$qrUrl = "https://geokrety.org/templates/qr2/qr.php?d=https://geokrety.org/m/qr.php?nr={$kret_tracking}";
$qr = imagecreatefromstring(file_get_contents($qrUrl));
$qr = imagecropauto($qr, IMG_CROP_WHITE);

$czarny = imagecolorallocate($img, 0, 0, 0);

// Headers are Kuba Reczny 2005 font
$font = '../fonts/lucida.ttf';

imagettftext($img, 35, 0, 390, 100, $czarny, $font, "$kret_nazwa");
imagettftext($img, 35, 0, 390, 245, $czarny, $font, $kret_owner);
imagettftext($img, 25, 0, 60, 380, $czarny, $font, StringUtils::mb_wordwrap(stripcslashes(strip_tags($kret_opis, '<img>')), 36));

imagettftext($img, 35, 0, 870, 70, $czarny, $font, $kret_tracking);
imagettftext($img, 35, 0, 1270, 70, $czarny, $font, $kret_id);

// Insert QR codes
imagecopyresampled($img, $qr, 55, 126, 0, 0, 130, 130, imagesx($qr), imagesy($qr));
imagecopyresampled($img, $qr, 1375, 126, 0, 0, 130, 130, imagesx($qr), imagesy($qr));

header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);
