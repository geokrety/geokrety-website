<?php

use Carbon\Carbon;

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.print_date_iso_format.php
 * Type:     modifier
 * Name:     print_date_iso_format
 * Purpose:  outputs a date time as isoformat
 * -------------------------------------------------------------.
 */
function smarty_modifier_print_date_iso_format(DateTime $date, string $isoFormat = 'lll', string $format = 'c'): string {
    return sprintf(
        '<span data-datetime="%s" title="%s">%s</span>',
        $date->format($format),
        $date->format($format),
        Carbon::parse($date->format('c'))->isoFormat($isoFormat)
    );
}
