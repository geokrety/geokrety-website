<?php

class StringUtils {
    /**
     * UTF-compatible wordwrap function.
     *
     * @see https://stackoverflow.com/a/4988494/1657819
     *
     * @param string $str   source string to split
     * @param int    $width desired width in symbols
     * @param string $break
     * @param bool   $cut
     *
     * @return string
     */
    public static function mb_wordwrap($str, $width = 75, $break = "\n", $cut = false) {
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
}
