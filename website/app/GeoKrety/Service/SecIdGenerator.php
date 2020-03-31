<?php

namespace GeoKrety\Service;

class SecIdGenerator {
    public static function generate(int $len = 42): string {
        $seed = str_split(str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $len));
        shuffle($seed);
        $rand = '';
        foreach (array_rand($seed, GK_SITE_SECID_CODE_LENGTH) as $k) {
            $rand .= $seed[$k];
        }
        return $rand;
    }
}
