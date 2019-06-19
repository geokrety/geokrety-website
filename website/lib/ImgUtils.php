<?php

class ImgUtils {
    /**
     * Writes center-aligned text to the image.
     *
     * @todo doesn't really work with 90/270 rotated texts
     *
     * @param $im resource image to write the text to
     * @param $size int size of the text
     * @param $angle int how to rotate the text
     * @param $x int point where to put the text
     * @param $y int point where to put the text
     * @param $color int color index
     * @param $font string path to font file
     * @param $string string actual text to write
     */
    public static function writeCenteredText($im, $size, $angle, $x, $y, $color, $font, $string) {
        $box = imagettfbbox($size, $angle, $font, $string);
        $xr = abs(max($box[2], $box[4]));
        $yr = abs(max($box[5], $box[7]));
        $x = intval(($x - $xr) / 2);
        $y = intval(($y + $yr) / 2);
        imagettftext($im, $size, $angle, $x, $y, $color, $font, $string);
    }

    /**
     * Writes right-aligned text to the image.
     *
     * @todo doesn't really work with 90/270 rotated texts
     *
     * @param $im resource image to write the text to
     * @param $size int size of the text
     * @param $angle int how to rotate the text
     * @param $x int point where to put the text
     * @param $y int point where to put the text
     * @param $color int color index
     * @param $font string path to font file
     * @param $string string actual text to write
     */
    public static function writeRightAlignedText($im, $size, $angle, $x, $y, $color, $font, $string) {
        $box = imagettfbbox($size, $angle, $font, $string);
        $textWidth = abs($box[4] - $box[0]);
        imagettftext($im, $size, $angle, $x - $textWidth, $y, $color, $font, $string);
    }
}
