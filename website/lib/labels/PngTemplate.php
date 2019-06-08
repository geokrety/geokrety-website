<?php

abstract class PngTemplate extends Template {
    public function getQrCode($trackingCode) {
        $qrUrl = "https://geokrety.org/templates/qr2/qr.php?d=https://geokrety.org/m/qr.php?nr={$trackingCode}";
        $qr = imagecreatefromstring(file_get_contents($qrUrl));
        $qr = imagecropauto($qr, IMG_CROP_WHITE);

        return $qr;
    }

    public function printPng($img) {
        // Useful when save file as png, not as PHP
        header('Content-Disposition: inline; filename="geokrety.png"');

        header('Content-Type: image/png');
        imagepng($img);
        imagedestroy($img);
    }

    public function getFont($font = 'lucida.ttf') {
        return '../fonts/'.$font;
    }
}
