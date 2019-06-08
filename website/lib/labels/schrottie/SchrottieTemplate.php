<?php

class SchrottieTemplate extends PngTemplate {
    public function getName() {
        return 'Modern :: Schrottie';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        $imgname = __DIR__.'/label.png';
        $img = imagecreatefrompng($imgname);

        $blackColor = imagecolorallocate($img, 0, 0, 0);
        $whiteColor = imagecolorallocate($img, 255, 255, 255);

        $font = $this->getFont('DejaVuSans.ttf');
        $font2 = $this->getFont('DejaVuSansCondensed.ttf');

        imagesavealpha($img, true);

        imagettftext($img, 19, 0, 270, 42, $blackColor, $font, $gkName);

        imagettftext($img, 19, 0, 270, 120, $blackColor, $font, $owner);

        imagettftext($img, 19, 0, 270, 195, $blackColor, $font, $gkId);
        imagettftext($img, 19, 0, 270, 272, $whiteColor, $font, $trackingCode);

        imagettftext($img, 21, 0, 500, 42, $blackColor, $font, $gkName);
        imagettftext($img, 14, 0, 500, 72, $blackColor, $font2, wordwrap(stripcslashes(strip_tags($comment, '<img>')), 50));

        $this->printPng($img);
    }
}
