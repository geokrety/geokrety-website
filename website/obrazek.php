<?php

function obrazek($alfabet = 'a c d e f h i k m n p s t w x y z 1 2 3 4 5 7 8')
{
    // generates antispam token + picture śćńółż
    include 'templates/konfig.php';
    include_once 'random_string.php';
    $STRING = random_string(6, $alfabet);

    //        header("Content-type: image/png");
    $im = @imagecreate(105, 32);
    $font = ('templates/font.ttf');
    $background_color = imagecolorallocate($im, 0, 0, 0);
    $text_color = imagecolorallocate($im, 233, 134, 91);
    imagettftext($im, 20, 0, 12, 24, $text_color, $font, $STRING);
    imagepng($im, $config['generated'].'obrazek.png');
    imagedestroy($im);

    $crypt = crypt($STRING, $config['sol']);

    $link = DBConnect();
    mysqli_select_db($link, $config['db']);

    $result = mysqli_query($link, "INSERT INTO `gk-aktywnekody` (`kod`) VALUES ('$crypt')") or $TRESC = 'Error #1111sql';
    mysqli_close($link);

    // tymczasowa inspekcja kodow w celu ustalenia
    // poziomu glupoty w organizmie uzytkownikow
    include_once 'defektoskop.php';
    errory_add('CAPTCHA: '.$STRING, 0, 'captcha');
    // koniec badania

    return '<input type="hidden" name="antyspamer" value="'.strtr($crypt, array($config['sol'] => '')).'" />';
}
