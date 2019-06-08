<?php

class SanSanchozTemplate1 extends PngTemplate {
    public function getName() {
        return 'Key chain HR :: QR :: SanSanchoz';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        $imgname = __DIR__.'/label.png';
        $img = imagecreatefrompng($imgname);

        $black = imagecolorallocate($img, 0, 0, 0);

        $font = $this->getFont('DejaVuSansCondensed-Bold.ttf');

        ImgUtils::writeCenteredText($img, 65, 0, 1000, 280, $black, $font, $gkName);
        ImgUtils::writeRightAlignedText($img, 55, 0, 1100, 270, $black, $font, 'by '.$owner);

        imagettftext($img, 75, 90, 1500, 640, $black, $font, $trackingCode);
        imagettftext($img, 65, 0, 680, 630, $black, $font, $gkId);

        // Insert QR codes
        $qr = $this->getQrCode($trackingCode);
        $qr = imagerotate($qr, 90, 0);
        imagecopyresampled($img, $qr, 1575, 126, 0, 0, 580, 580, imagesx($qr), imagesy($qr));

        $this->printPng($img);
    }
}
