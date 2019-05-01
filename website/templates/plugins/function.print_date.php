<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.print_date.php
 * Type:     function
 * Name:     print_date
 * Purpose:  outputs a date time as relative
 * -------------------------------------------------------------
 */
function smarty_function_print_date(array $params, Smarty_Internal_Template $template) {
    if (!in_array('date', array_keys($params)) || empty($params['date'])) {
        trigger_error("print_date: missing 'date' parameter");

        return;
    }

    $date = $params['date'];

    return '<span data-datetime="'.$date->format('c').'" title="'.$date->format('c').'">'.\Carbon\Carbon::parse($date->format('c'))->diffForHumans().'</span>';
}
