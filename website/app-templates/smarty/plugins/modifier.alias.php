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

    $f3 = \Base::instance();
    // Workaround for https://github.com/bcosca/fatfree-core/issues/345
    if (!$f3->exists('__WORKAROUND_1__')) {
        foreach ($f3->get('PARAMS') as $k => $v) {
            if ($k === 0) {
                continue;
            }
            $f3->set("PARAMS.$k", urlencode($v));
        }
        $f3->set('__WORKAROUND_1__', true);
    }

    return GK_SITE_BASE_SERVER_URL.$f3->alias($string, $params ?? [], $query).$fragment;
}
