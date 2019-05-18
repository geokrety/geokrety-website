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
    if (!in_array('lang', array_keys($params)) || empty($params['lang'])) {
        trigger_error("language: missing 'lang' parameter");

        return;
    }

    $lang = $params['lang'];

    $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();
    $languages = $isoCodes->getLanguages();
    $language = $languages->getByAlpha2($lang);

    return $language->getName();
}
