<?php

require_once '__sentry.php';

// Call the user's language
// search-ajax has the same without include

$lang = 'en_US.UTF-8';

if (!empty($_GET['lang'])) {
    $lang = $_GET['lang'];
}        // setting lang via url parameter

elseif (!empty($_COOKIE['geokret1'])) {
    $lang = $_COOKIE['geokret1'];
}     // setting lang via cookie

else {
    // choose language  according to the browser setting
    include 'templates/konfig.php';    // config
    include 'lang-accept.php';
    $lang = al2gt($config_jezyk_encoding);

    // if no lang definet, defaulting to english
    if ($lang == '' or !isset($lang)) {
        $lang = 'en_US.UTF-8';
    }
}

// Prevent passing unwanted data
require_once 'templates/konfig-local.php';
$langShortcode = substr($lang, 0, 2);
if (!array_key_exists($langShortcode, $config_jezyk_encoding)) {
    $lang = 'en_US.UTF-8';
} else {
    $lang = $config_jezyk_encoding[$langShortcode];
}

//putenv("LC_ALL=$lang"); //fox windows only
putenv("LANG=$lang"); //fox windows only
//putenv("LANGUAGE=$lang"); //fox windows only

require_once 'templates/konfig-local.php';
@setlocale(LC_MESSAGES, $lang);
setlocale(LC_TIME, $lang);
setlocale(LC_NUMERIC, 'en_EN');
bindtextdomain('messages', BINDTEXTDOMAIN_PATH);
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');
