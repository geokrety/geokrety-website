<?php

class DenSolTemplate1 extends PngTemplate
{
    public function getName()
    {
        return 'Key chain HR :: QR :: DenSol';
    }

    public function generate($gkId, $trackingCode, $gkName, $owner, $comment = '', $languages = ['en'])
    {
        $imgname = __DIR__ . '/label.png';
        $img = imagecreatefrompng($imgname);

        $blackColor = imagecolorallocate($img, 0, 0, 0);
        $whiteColor = imagecolorallocate($img, 255, 255, 255);

        $fontOwner = $this->getFont('DejaVuSerif-Italic.ttf');
        $font = $this->getFont('DejaVuSansCondensed-Bold.ttf');

        ImgUtils::writeCenteredText($img, 35, 0, 590, 150, $blackColor, $font, $gkName);
        imagettftext($img, 30, 0, 330, 210, $blackColor, $fontOwner, 'by ' . $owner);
        imagettftext($img, 35, 0, 470, 445, $blackColor, $font, $gkId);


        ImgUtils::writeCenteredText($img, 65, 90, 1900, 820, $whiteColor, $font, $trackingCode);

        $qr = $this->getQrCode($trackingCode);
        // Insert QR codes
        $qr = imagerotate($qr, 90, 0);
        imagecopyresampled($img, $qr, 1000, 80, 0, 0, 350, 350, imagesx($qr), imagesy($qr));

        $refText = "REFERENCE NUMBER:";
        $trackingText = "TRACKING CODE:";
        $yPos = 800;
        if (in_array("ru", $languages)) {
            $refText = "РЕФЕРЕНС НОМЕР:";
            $trackingText = "КОД ОТСЛЕЖИВАНИЯ:";
            $yPos = 910;
        }
        imagettftext($img, 20, 0, 430, 370, $blackColor, $font, $refText);
        ImgUtils::writeCenteredText($img, 26, 90, 1690, $yPos, $blackColor, $font, $trackingText);

        $this->printPng($img);
    }
}
