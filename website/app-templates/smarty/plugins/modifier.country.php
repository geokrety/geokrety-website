<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.country.php
 * Type:     modifier
 * Name:     country
 * Purpose:  outputs a flag for a country
 * -------------------------------------------------------------
 */
function smarty_modifier_country(string $countryCode) {
    $countryCode = smarty_modifier_escape($countryCode);
    // TODO localize country name in title
    return '<span class="flag-icon flag-icon-'.$countryCode.'" title="'.$countryCode.'"></span>';
}
