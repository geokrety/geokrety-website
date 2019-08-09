<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.language.php
 * Type:     function
 * Name:     language
 * Purpose:  outputs a language name based on ISO code
 * -------------------------------------------------------------
 */
function smarty_function_language(array $params, Smarty_Internal_Template $template) {
    if (!in_array('lang', array_keys($params))) {
        trigger_error("language: missing 'lang' parameter");

        return;
    }

    if (empty($params['lang'])) {
        return;
    }

    $lang = $params['lang'];
    $language = \Geokrety\Service\LanguageService::getLanguageByAlpha2($lang);

    if (in_array('locale', array_keys($params)) && $params['lang']) {
        return $language->getLocalName(); // українська
    }

    return $language->getName(); // Ukrainian
}
