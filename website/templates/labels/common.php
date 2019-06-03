<?php

// Default `wordwrap` doesn't work with UTF8 as expected.
// Here is code from https://stackoverflow.com/a/4988494/1657819
function mb_wordwrap($str, $width = 75, $break = "\n", $cut = false) {
    $lines = explode($break, $str);
    foreach ($lines as &$line) {
        $line = rtrim($line);
        if (mb_strlen($line) <= $width) {
            continue;
        }
        $words = explode(' ', $line);
        $line = '';
        $actual = '';
        foreach ($words as $word) {
            if (mb_strlen($actual.$word) <= $width) {
                $actual .= $word.' ';
            } else {
                if ($actual != '') {
                    $line .= rtrim($actual).$break;
                }
                $actual = $word;
                if ($cut) {
                    while (mb_strlen($actual) > $width) {
                        $line .= mb_substr($actual, 0, $width).$break;
                        $actual = mb_substr($actual, $width);
                    }
                }
                $actual .= ' ';
            }
        }
        $line .= trim($actual);
    }

    return implode($break, $lines);
}

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
function writeCenteredText($im, $size, $angle, $x, $y, $color, $font, $string) {
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
function writeRightAlignedText($im, $size, $angle, $x, $y, $color, $font, $string) {
    $box = imagettfbbox($size, $angle, $font, $string);
    $textWidth = abs($box[4] - $box[0]);
    imagettftext($im, $size, $angle, $x - $textWidth, $y, $color, $font, $string);
}
