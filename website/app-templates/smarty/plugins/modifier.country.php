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
function smarty_modifier_country(?string $countryCode): string {
    if (is_null($countryCode)) {
        $countryCode = 'xyz';
    }
    $countryCode = smarty_modifier_escape($countryCode);
    // TODO localize country name in title
    return sprintf('<span class="flag-icon flag-icon-%s" title="%s"></span>', $countryCode, $countryCode);
}
