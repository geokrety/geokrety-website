<?php

require_once '__sentry.php';

require 'templates/konfig.php';    // config
require 'aktualizuj.php';

$link = DBConnect();

for ($i = 2; $i < 5; ++$i) {
    //for($i=1; $i<2; $i++){
    echo "$i<br />";
    aktualizuj_obrazek_statystyki("$i");
}

//aktualizuj_obrazek_statystyki(1);
