<?php

use Carbon\Carbon;

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.print_date.php
 * Type:     modifier
 * Name:     print_date
 * Purpose:  outputs a date time as relative
 * -------------------------------------------------------------.
 */
function smarty_modifier_print_date(DateTime $date, string $format = 'c'): string {
    return sprintf(
        '<span data-datetime="%s" title="%s">%s</span>',
        $date->format($format),
        $date->format($format),
        Carbon::parse($date->format('c'))->diffForHumans()
    );
}