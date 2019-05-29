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
