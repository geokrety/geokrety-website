<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.country_flag.php
 * Type:     function
 * Name:     country_flag
 * Purpose:  outputs a flag for a country
 * -------------------------------------------------------------
 */
function smarty_function_country_flag(array $params, Smarty_Internal_Template $template) {
    if (!in_array('country', array_keys($params)) || empty($params['country'])) {
        return;
    }
    return '<span class="flag-icon flag-icon-'.$params['country'].'" title="'.$params['country'].'"></span>';
}
