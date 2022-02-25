<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.alias.php
 * Type:     modifier
 * Name:     alias
 * Purpose:  return the url of the given alias
 * -------------------------------------------------------------.
 */
function smarty_modifier_alias($string, $params = null, $query = null, $fragment = null): string {
    if (!is_null($fragment) && substr($fragment, 0, 1) !== '#') {
        $fragment = '#'.$fragment;
    }

    return GK_SITE_BASE_SERVER_URL.\Base::instance()->alias($string, $params, $query).$fragment;
}
