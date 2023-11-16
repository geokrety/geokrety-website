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

use GeoKrety\Service\LanguageService;

function smarty_modifier_language(?string $lang, bool $asLocale = false): string {
    return LanguageService::getLanguageByAlpha2($lang ?? 'en', $asLocale);
}
