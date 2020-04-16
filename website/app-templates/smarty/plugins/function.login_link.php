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
    return \GeoKrety\Service\Url::getGoto('login');
}
