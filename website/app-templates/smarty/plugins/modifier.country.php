<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.country.php
 * Type:     modifier
 * Name:     country
 * Purpose:  outputs a flag for a country
 * -------------------------------------------------------------.
 */
/**
 * @throws \Exception
 */
function smarty_modifier_country(?string $countryCode, string $output = 'css'): string {
    if (is_null($countryCode)) {
        $countryCode = 'xyz';
    }
    $countryCode = smarty_modifier_escape($countryCode);
    // TODO localize country name in title
    if ($output === 'css') {
        return sprintf('<span class="flag-icon flag-icon-%s" title="%s"></span>', $countryCode, $countryCode);
    } elseif ($output === 'html') {
        return sprintf('<img src="https://cdn.geokrety.org/flags/4x3/%s.svg" class="w-4 d-inline-block" width="16" title="%s">', $countryCode, $countryCode);
    }
    throw new Exception('smarty_modifier_country(): Unsupported output mode');
}
