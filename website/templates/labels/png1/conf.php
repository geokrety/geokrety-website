<?php

/*
 * Geokrety label template
 *
 * Template name: Modern :: Wallson 1
 */

require_once 'common.php';

$imgname = "$kret_szablon/geokret_label_v1.png";
$img = imagecreatefrompng($imgname); /* Attempt to open */

$czarny = imagecolorallocate($img, 0, 0, 0);

// Headers are Kuba Reczny 2005 font
$font = '../fonts/lucida.ttf';

imagettftext($img, 35, 0, 600, 100, $czarny, $font, "$kret_nazwa");
imagettftext($img, 35, 0, 600, 287, $czarny, $font, $kret_owner);
imagettftext($img, 25, 0, 600, 500, $czarny, $font, mb_wordwrap(stripcslashes(strip_tags($kret_opis, '<img>')), 35));

imagettftext($img, 35, 0, 1420, 100, $czarny, $font, $kret_tracking);
imagettftext($img, 35, 0, 2010, 100, $czarny, $font, $kret_id);

$manual_strings = array(
    'pl' => 'Instrukcja obsługi: 1. Zabierz geokreta. Zanotuj tracking code 2. Ukryj kreta w innym keszu. 3. Zarejestruj jego podróż na stronie https://geokrety.org',
    'en' => "User's manual: 1. Take this GeoKret. Please note down his Tracking Code. 2. Hide in another cache. 3. Register the trip at https://geokrety.org",
    'de' => 'Anleitung: 1. Nimm den GeoKret mit and notiere Dir den Tracking Code. 2. Verstecke ihn wieder in einem anderen Cache. 3. Logge seine Reise auf https://geokrety.org',
    'cz' => 'Navod pro uživatele: 1. Vem Geokrtka. Poznamenej si jeho Tracking Code. 2. Schovej ho v jiné kešce. 3. Registruj cestu na https://geokrety.org',
    'fr' => "Manuel de l'utilisateur: 1. Prenez ce GeoKret et notez son code de suivi. 2. Déposez le dans une autre cache. 3. Enregistrez son voyage sur https://geokrety.org",
    'ru' => 'Руководство пользователя: 1. Возьмите ГеоКрота. Запишите его Tracking Code. 2. Переместите его в другой тайник. 3. Сообщите об этом на https://geokrety.org',
);

// First manuals column
imagettftext($img, 18, 0, 1245, 220, $czarny, $font, mb_wordwrap(implode("\n\n", [$manual_strings['pl'], $manual_strings['en'], $manual_strings['de']]), 45));

// Second manuals column
imagettftext($img, 18, 0, 1800, 220, $czarny, $font, mb_wordwrap(implode("\n\n", [$manual_strings['cz'], $manual_strings['fr'], $manual_strings['ru']]), 45));

header('Content-Type: image/jpeg');
imagejpeg($img);
imagedestroy($img);
