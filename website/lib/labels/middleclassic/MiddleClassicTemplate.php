<?php

class MiddleClassicTemplate extends PngTemplate {
    public function getName() {
        return 'Middle classic :: filips';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        $imgname = __DIR__.'/label.png';
        $img = imagecreatefrompng($imgname);
        $blackColor = imagecolorallocate($img, 0, 0, 0);

        imagesavealpha($img, true);

        imagettftext($img, 15, 0, 24, 149, $blackColor, $this->getFont(), "$gkName by $owner");
        imagettftext($img, 10, 0, 24, 170, $blackColor, $this->getFont(), wordwrap(stripcslashes(strip_tags($comment, '<img>')), 50));

        imagettftext($img, 15, 0, 214, 110, $blackColor, $this->getFont(), $trackingCode);
        imagettftext($img, 15, 0, 24, 110, $blackColor, $this->getFont(), $gkId);

        $this->printPng($img);
    }
}
