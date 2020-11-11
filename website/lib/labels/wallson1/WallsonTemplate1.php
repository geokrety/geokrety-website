<?php

class WallsonTemplate1 extends PngTemplate {
    public function getName() {
        return 'Modern 1 :: wallson';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en']) {
        $imgname = __DIR__.'/label.png';
        $img = imagecreatefrompng($imgname);

        $blackColor = imagecolorallocate($img, 0, 0, 0);

        imagettftext($img, 35, 0, 600, 100, $blackColor, $this->getFont(), $gkName);
        imagettftext($img, 35, 0, 600, 287, $blackColor, $this->getFont(), $owner);
        imagettftext($img, 25, 0, 600, 500, $blackColor, $this->getFont(), StringUtils::mb_wordwrap(stripcslashes(strip_tags($comment, '<img>')), 32));

        imagettftext($img, 35, 0, 1420, 100, $blackColor, $this->getFont(), $trackingCode);
        imagettftext($img, 35, 0, 2010, 100, $blackColor, $this->getFont(), $gkId);

        $i = 0;
        $languagesColumn = [];
        foreach ($languages as $lang) {
            $lang = $this->getManual($lang);
            if (empty($lang)) {
                continue;
            }
            $languagesColumn[] = $lang;
            ++$i;

            if ($i % 3 == 0) {
                //We got three manuals, let's push them to the column
                imagettftext($img, 18, 0, 1245 + ($i / 3 - 1) * 555, 220, $blackColor, $this->getFont(), StringUtils::mb_wordwrap(implode("\n\n", $languagesColumn), 45));
                $languagesColumn = [];
            }
        }

        if (count($languagesColumn) > 0) {
            imagettftext($img, 18, 0, 1245 + (ceil($i / 3) - 1) * 555, 220, $blackColor, $this->getFont(), StringUtils::mb_wordwrap(implode("\n\n", $languagesColumn), 45));
        }

        $this->printPng($img);
    }
}
