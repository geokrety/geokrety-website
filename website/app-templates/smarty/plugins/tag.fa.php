<?php

const FA_ICON_SIZES = ['lg', '2x', '3x', '4x', '5x'];
/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.fa.php
 * Type:     function
 * Name:     fa
 * Purpose:  outputs a font awsome icon
 * -------------------------------------------------------------.
 */
function smarty_tag_fa(array $params, Smarty\Template $template): string {
    if (!in_array('icon', array_keys($params)) || empty($params['icon'])) {
        trigger_error("fa: missing 'icon' parameter");

        return '';
    }
    $class = 'fa-'.$params['icon'];

    if (in_array('size', array_keys($params)) && !empty($params['size'])) {
        if (!in_array($params['size'], FA_ICON_SIZES)) {
            trigger_error("fa: wrong 'size' parameter value.");

            return '';
        }
        $class .= ' fa-'.$params['size'];
    }

    if (in_array('title', array_keys($params)) && !empty($params['title'])) {
        $title = $params['title'];
    }

    return sprintf(
        '<i class="fa %s"%s></i>',
        $class,
        isset($title) ? sprintf(' title="%s"', $title) : ''
    );
}
