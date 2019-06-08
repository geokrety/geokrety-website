<?php

class ClassicTemplate extends PngTemplate {
    public function getName() {
        return 'Classic :: filips';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        $imgname = __DIR__.'/label.png';
        $img = imagecreatefrompng($imgname);
        $blackColor = imagecolorallocate($img, 0, 0, 0);

        imagesavealpha($img, true);

        imagettftext($img, 17, 0, 61, 143, $blackColor, $this->getFont(), $gkName);
        imagettftext($img, 17, 0, 373, 143, $blackColor, $this->getFont(), $owner);
        imagettftext($img, 13, 0, 61, 291, $blackColor, $this->getFont(), wordwrap(stripcslashes(strip_tags($comment, '<img>')), 66));

        imagettftext($img, 17, 0, 61, 221, $blackColor, $this->getFont(), $gkId);
        imagettftext($img, 17, 0, 379, 221, $blackColor, $this->getFont(), $trackingCode);

        $this->printPng($img);
    }
}
