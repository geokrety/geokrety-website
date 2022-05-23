<?php

class SanSanchozTemplate2 extends PngTemplate {
    public function getName() {
        return 'Key chain VR :: QR :: SanSanchoz';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        $imgname = __DIR__.'/label.png';
        $img = imagecreatefrompng($imgname);

        $black = imagecolorallocate($img, 0, 0, 0);

        $fontOwner = $this->getFont('DejaVuSerif-Italic.ttf');
        $font = $this->getFont('DejaVuSansCondensed-Bold.ttf');

        ImgUtils::writeCenteredText($img, 50, 0, 460, 1430, $black, $font, $gkName);
        ImgUtils::writeCenteredText($img, 35, 0, 460, 2360, $black, $fontOwner, 'by '.$owner);

        imagettftext($img, 55, 0, 70, 1130, $black, $font, $gkId);
        imagettftext($img, 50, 180, 410, 470, $black, $font, $trackingCode);

        // Insert QR code
        $qr = $this->getQrCode($trackingCode);
        $qr = imagerotate($qr, 180, 0);
        imagecopyresampled($img, $qr, 80, 126, 0, 0, 310, 310, imagesx($qr), imagesy($qr));

        $this->printPng($img);
    }
}
