<?php

class WallsonTemplate2 extends PngTemplate {
    public function getName() {
        return 'Modern 2 :: wallson';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        $imgname = __DIR__.'/label.png';
        $img = imagecreatefrompng($imgname);

        $blackColor = imagecolorallocate($img, 0, 0, 0);

        // Headers are Kuba Reczny 2005 font

        imagettftext($img, 35, 0, 390, 100, $blackColor, $this->getFont(), $gkName);
        imagettftext($img, 35, 0, 390, 245, $blackColor, $this->getFont(), $owner);
        imagettftext($img, 25, 0, 60, 380, $blackColor, $this->getFont(), StringUtils::mb_wordwrap(stripcslashes(strip_tags($comment, '<img>')), 36));

        imagettftext($img, 35, 0, 870, 70, $blackColor, $this->getFont(), $trackingCode);
        imagettftext($img, 35, 0, 1270, 70, $blackColor, $this->getFont(), $gkId);

        $qr = $this->getQrCode($trackingCode);
        // Insert QR codes
        imagecopyresampled($img, $qr, 55, 126, 0, 0, 130, 130, imagesx($qr), imagesy($qr));
        imagecopyresampled($img, $qr, 1375, 126, 0, 0, 130, 130, imagesx($qr), imagesy($qr));

        $this->printPng($img);
    }
}
