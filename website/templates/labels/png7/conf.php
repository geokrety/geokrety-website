<?php

/*
 * Geokrety label template
 *
 * Template name: Key chain vertical + QR
 * Template design: SanSanchoz
 */

$imgname = "$kret_szablon/geokret_label.png";
$img = imagecreatefrompng($imgname);

$qrUrl = "https://geokrety.org/templates/qr2/qr.php?d=https://geokrety.org/m/qr.php?nr={$kret_tracking}";
$qr = imagecreatefromstring(file_get_contents($qrUrl));
$qr = imagecropauto($qr, IMG_CROP_WHITE);
$qr = imagerotate($qr, 180, 0);

$black = imagecolorallocate($img, 0, 0, 0);

$fontOwner = '../fonts/DejaVuSerif-Italic.ttf';
$font = '../fonts/DejaVuSansCondensed-Bold.ttf';

ImgUtils::writeCenteredText($img, 50, 0, 460, 1430, $black, $font, "$kret_nazwa");
ImgUtils::writeCenteredText($img, 35, 0, 460, 2360, $black, $fontOwner, 'by '.$kret_owner);

imagettftext($img, 55, 0, 70, 1130, $black, $font, $kret_tracking);
imagettftext($img, 50, 180, 410, 470, $black, $font, $kret_id);

// Insert QR codes
imagecopyresampled($img, $qr, 80, 126, 0, 0, 310, 310, imagesx($qr), imagesy($qr));

header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);
