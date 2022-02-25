<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.language.php
 * Type:     modifier
 * Name:     language
 * Purpose:  outputs a language name based on ISO code
 * -------------------------------------------------------------.
 */
function smarty_modifier_language(string $lang, bool $asLocale = false) {
    return \GeoKrety\Service\LanguageService::getLanguageByAlpha2($lang, $asLocale);
}
