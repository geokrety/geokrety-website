<?php

class SchrottieTemplate2 extends PngTemplate {
    public function getName() {
        return 'Modern :: Schrottie :: Side Track/QR';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        $imgname = __DIR__.'/label.png';
        $img = imagecreatefrompng($imgname);

        $blackColor = imagecolorallocate($img, 0, 0, 0);
        $whiteColor = imagecolorallocate($img, 255, 255, 255);

        $font = $this->getFont('DejaVuSans.ttf');

        imagesavealpha($img, true);

        imagettftext($img, 19, 0, 270, 42, $blackColor, $font, $gkName);
        imagettftext($img, 19, 0, 270, 120, $blackColor, $font, $owner);
        imagettftext($img, 19, 0, 270, 195, $blackColor, $font, $gkId);

        imagettftext($img, 21, 0, 500, 42, $blackColor, $font, $gkName);
        imagettftext($img, 19, 0, 750, 45, $whiteColor, $font, $trackingCode);

        $qr = $this->getQrCode($trackingCode);
        // Insert QR codes
        imagecopyresampled($img, $qr, 500, 60, 0, 0, 200, 200, imagesx($qr), imagesy($qr));

        $this->printPng($img);
    }
}
