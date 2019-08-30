<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.login_link.php
 * Type:     function
 * Name:     login_link
 * Purpose:  outputs a font awsome icon
 * -------------------------------------------------------------
 */
function smarty_function_login_link() {
    $f3 = \Base::instance();
    $query = array(
        'goto' => urlencode($f3->get('GET.goto')),
        'params' => urlencode(base64_encode($f3->serialize($f3->get('PARAMS')))),
    );

    return GK_SITE_BASE_SERVER_URL.\Base::instance()->alias('login', null, $query);
}
