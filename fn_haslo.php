<?php

// funkcje tworzenia haszu i porównywania haszu z hasłem

// benczmarking

// kodowanie
// 8) 0.02349591255188 sekund
// 9) 0.045217990875244 sekund
// 10) 0.089047908782959 sekund
// 11) 0.17849707603455 sekund
// 12) 0.36429190635681 sekund
// 13) 0.77093505859375 sekund
// 14) 1.5591759681702 sekund
// 15) 4.6916089057922 sekund

// dekodowanie
// password correct 8) 0.021777153015137 sekund
// password correct 9) 0.043589115142822 sekund
// password correct 10) 0.086626052856445 sekund
// password correct 11) 0.23867893218994 sekund
// password correct 12) 0.52684187889099 sekund
// password correct 13) 0.69566297531128 sekund
// password correct 14) 1.6809070110321 sekund
// password correct 15) 2.8649749755859 sekund

if (!function_exists(haslo_koduj)) {
    include 'templates/PasswordHash.php';
    if (!isset($config)) {
        include 'templates/konfig.php';
    }

    // tworzy hasz hasła

    function haslo_koduj($haslo)
    {
        global $t_hasher;
        global $config;
        $t_hasher = new PasswordHash(11, false);   // 11 - wartość na tyle duża, żeby nie było za szybko
        return $t_hasher->HashPassword($haslo.$config['sol2'].'127');    // hasło + sól z konfigu + sól lokalna :)
    }

    // porownuje hasło z haszem z bazy danych i zwraca wartość logiczną TRUE jeśli ok
    function haslo_sprawdz($haslo, $hash)
    {
        global $t_hasher;
        global $config;
        $t_hasher = new PasswordHash(11, false);   // 11 - wartość na tyle duża, żeby nie było za szybko
        return $t_hasher->CheckPassword($haslo.$config['sol2'].'127', $hash);    // hasło + sól z konfigu + sól lokalna :)
    }
}
