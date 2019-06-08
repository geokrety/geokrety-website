<?php

/*
 * Geokrety label template
 *
 * Template name: Key chain horizontal + QR
 * Template design: SanSanchoz
 */

$imgname = "$kret_szablon/geokret_label.png";
$img = imagecreatefrompng($imgname);

$qrUrl = "https://geokrety.org/templates/qr2/qr.php?d=https://geokrety.org/m/qr.php?nr={$kret_tracking}";
$qr = imagecreatefromstring(file_get_contents($qrUrl));
$qr = imagecropauto($qr, IMG_CROP_WHITE);
$qr = imagerotate($qr, 90, 0);

$black = imagecolorallocate($img, 0, 0, 0);

$font = '../fonts/DejaVuSansCondensed-Bold.ttf';

ImgUtils::writeCenteredText($img, 65, 0, 1000, 280, $black, $font, "$kret_nazwa");
ImgUtils::writeRightAlignedText($img, 55, 0, 1100, 270, $black, $font, 'by '.$kret_owner);

imagettftext($img, 75, 90, 1500, 640, $black, $font, $kret_tracking);
imagettftext($img, 65, 0, 680, 630, $black, $font, $kret_id);

// Insert QR codes
imagecopyresampled($img, $qr, 1575, 126, 0, 0, 580, 580, imagesx($qr), imagesy($qr));

header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);
