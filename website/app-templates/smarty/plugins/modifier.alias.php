<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.alias.php
 * Type:     modifier
 * Name:     alias
 * Purpose:  return the url of the given alias
 * -------------------------------------------------------------
 */
function smarty_modifier_alias($string, $params = '', $query = '') {
    return GK_SITE_BASE_SERVER_URL.\Base::instance()->alias($string, $params, $query);
}
