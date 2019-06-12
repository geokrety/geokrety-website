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

    /**
     * Fills transparent image with given background color.
     *
     * @param $img resource transparent image to fill
     * @param array $bgColor background color, white by default
     *
     * @return resource
     */
    public function fillBackground($img, $bgColor = ['red' => 255, 'green' => 255, 'blue' => 255]) {
        $width = imagesx($img);
        $height = imagesy($img);

        //create new image and fill with background color
        $backgroundImg = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($backgroundImg, $bgColor['red'], $bgColor['green'], $bgColor['blue']);
        imagefill($backgroundImg, 0, 0, $color);

        //copy original image to background
        imagecopy($backgroundImg, $img, 0, 0, 0, 0, $width, $height);

        return $backgroundImg;
    }

    public function getFont($font = 'lucida.ttf') {
        return '../fonts/'.$font;
    }
}
