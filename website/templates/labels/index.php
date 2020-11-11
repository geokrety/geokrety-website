<?php

require '../../__sentry.php';   // Error catching

$kret_helplang = $_POST['helplang'];
$kret_id = $_POST['id'];
$kret_nazwa = $_POST['nazwa'];
$kret_opis = $_POST['opis'];
$kret_owner = $_POST['owner'];
$kret_szablon = $_POST['szablon'];
$kret_tracking = $_POST['tracking'];

/** @var Template $template */
$template = new $kret_szablon();

if (!isset($kret_helplang)) {
    $kret_helplang = ['en'];
} elseif (!in_array('en', $kret_helplang)) {
    $kret_helplang[] = 'en';
}

return $template->generate($kret_id, $kret_tracking, $kret_nazwa, $kret_owner, $kret_opis, $kret_helplang);
