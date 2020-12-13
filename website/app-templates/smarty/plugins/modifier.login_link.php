<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.login_link.php
 * Type:     function
 * Name:     login_link
 * Purpose:  outputs a font awesome icon
 * -------------------------------------------------------------
 */
function smarty_modifier_login_link(string $alias = 'login', $params = null) {
    return \GeoKrety\Service\Url::serializeGoto($alias, $params);
}
