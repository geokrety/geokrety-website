<?php

use Geokrety\Service\LanguageService;

$lang = $_GET['lang'] ?? LanguageService::DEFAULT_LANGUAGE_CODE;
$lang = LanguageService::isLanguageSupported($lang) ? $lang : LanguageService::DEFAULT_LANGUAGE_CODE;
define('LANGUAGE', $lang);

putenv('LANGUAGE='.LANGUAGE);
setlocale(LC_MESSAGES, LANGUAGE);
setlocale(LC_TIME, LANGUAGE);
setlocale(LC_NUMERIC, 'en_EN');
bindtextdomain('messages', BINDTEXTDOMAIN_PATH);
bind_textdomain_codeset('messages', 'UTF-8');
textdomain('messages');

// # Ask user if he wish to switch language
// $supportedLanguages = LanguageService::SUPPORTED_LANGUAGES;
// $browserDetectedLanguage = http\Env::negotiateLanguage($supportedLanguages);
// if ($browserDetectedLanguage !== LANGUAGE) {
//     // redirect to right page
// }
