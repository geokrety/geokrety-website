<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.login_link.php
 * Type:     function
 * Name:     login_link
 * Purpose:  outputs a font awesome icon
 * -------------------------------------------------------------.
 */

use GeoKrety\Service\Url;

function smarty_modifier_login_link(string $alias = 'login', $params = null): string {
    return Url::serializeGoto($alias, $params);
}
