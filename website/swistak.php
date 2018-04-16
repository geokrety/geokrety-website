<?php

require 'templates/konfig.php';

class swistak
{
    private static $key = SWISTAK_KEY;
    private static $iv32 = SWISTAK_IV32;

    public static $alphabet_full = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    public static $alphabet_azAZ = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    private static $algorithm = MCRYPT_RIJNDAEL_128;

    public static function safe_b64encode($data)
    {
        $data = base64_encode($data);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);

        return $data;
    }

    public static function safe_b64decode($data)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        return base64_decode($data);
    }

    // max_repeat_chars - ile razy moze sie powtorzyc jakis znak  (np dla wartosci 0 i alfabetu 'ABCD', nigy nie uzyskamy ciagu 'AABC' i nigdy ciag nie bedzie dluzszy od alfabetu)

    public static function getRandomString($length, $max_repeat_chars = 2, $alphabet = '')
    {
        if (empty($alphabet)) {
            $alphabet = self::$alphabet_full;
        }
        if ($max_repeat_chars > 0) {
            $alphabet .= str_repeat($alphabet, $max_repeat_chars);
        }

        return substr(str_shuffle($alphabet), 0, $length);
    }

    private static function haszuj($data, $raw = true)
    {
        return md5($data, $raw);
    }

    // iv_length - dlugosc wygenerowanego iv (wektora inicjujacego), reszta (32-iv_length) jest stala w zmiennej $iv32. max to 32 bajty.
    // hash_length - dlugosc hasha dolaczona do wiadomosci. max to 16 bajtow bo jest to wartosc 'raw'.
    // *** nalezy pamietac ze jezeli podamy inne wartosci tych parametrow, to przy rozwijaniu musimy rowniez uzyc tych samych wartosci (!)

    public static function zawin($data, $iv_length = 4, $hash_length = 4)
    {
        if (!$data) {
            return false;
        }
        $iv_prefix = self::getRandomString($iv_length, 0);
        //$iv_size = mcrypt_get_iv_size(swistak::$algorithm, MCRYPT_MODE_OFB); //	echo "[[[$iv_size]]]";
        // $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $hash = substr(self::haszuj($data), 0, $hash_length);
        $enc = mcrypt_encrypt(self::$algorithm, md5(self::$key), $hash.$data, MCRYPT_MODE_OFB, $iv_prefix.substr(self::$iv32, 0, 16 - $iv_length));
        $b64 = self::safe_b64encode($enc);

        return $iv_prefix.$b64;
    }

    public static function rozwin($data, $iv_length = 4, $hash_length = 4)
    {
        if (!$data) {
            return false;
        }
        $iv_prefix = substr($data, 0, $iv_length);
        $b64 = substr($data, $iv_length);
        $dec = @mcrypt_decrypt(self::$algorithm, md5(self::$key), self::safe_b64decode($b64), MCRYPT_MODE_OFB, $iv_prefix.substr(self::$iv32, 0, 16 - $iv_length));
        $hash = substr($dec, 0, $hash_length);
        $cleartext = substr($dec, $hash_length);
        if ($hash != substr(self::haszuj($cleartext), 0, $hash_length)) {
            return false;
        }

        return trim($cleartext);
    }

    // ------------------------------------------------------------------------------------------------------------------------------

    // przyklady:

    // szyfruje stringa w postaci "aktualny_czas|sol"
    // static public function zawin_czas_i_sol($salt_length=3, $iv_length=4, $hash_length=4){
        // $random = swistak::getRandomString($salt_length, 1, swistak::$alphabet_full);
        // return swistak::zawin(time()."|$random", $iv_length, $hash_length);
    // }

    // odszyfrowuje "czas_i_sol" i zwraca tablice:
    // [0] - caly string
    // [1] - czas
    // [2] - sol
    // static public function rozwin_czas_i_sol($tas, $iv_length=4, $hash_length=4){
        // $data = swistak::rozwin($tas, $iv_length, $hash_length);
        // if (!$data){return false;}
        // if(preg_match("#^([\d]{10})\|([\S]+)$#i", $data, $matches))
            // return $matches;
        // else
            // return false;
    // }
}
