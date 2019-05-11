<?php

const SORT_TYPES = array(
    'numeric',
    'alpha',
    'amount',
);

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.sort.php
 * Type:     function
 * Name:     sort
 * Purpose:  outputs a sort link with icon for type
 * -------------------------------------------------------------
 */
function smarty_function_sort(array $params, Smarty_Internal_Template $template) {
    if (!in_array('column', array_keys($params)) || empty($params['column'])) {
        trigger_error("assign: empty 'column' parameter");

        return;
    }
    if (!in_array('type', array_keys($params)) || !in_array($params['type'], SORT_TYPES)) {
        trigger_error("assign: wrong 'type' parameter");

        return;
    }

    $get = $_GET;
    $icon = 'sort';
    if (isset($get['orderBy'])) {
        if ($get['orderBy'] == $params['column']) {
            $get['orderBy'] = '-'.$params['column'];
            $icon = 'sort-'.$params['type'].'-asc';
        } elseif ($get['orderBy'] == '-'.$params['column']) {
            $get['orderBy'] = $params['column'];
            $icon = 'sort-'.$params['type'].'-desc';
        } else {
            $get['orderBy'] = $params['column'];
        }
    } else {
        $get['orderBy'] = $params['column'];
    }

    $anchor = null;
    if (in_array('anchor', array_keys($params)) && !empty($params['anchor'])) {
        $anchor = '#'.$params['anchor'];
    }

    return '<a href="?'.http_build_query($get).$anchor.'">'.smarty_function_fa(['icon' => $icon], $template).'</a>';
}
