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
 *
 * @property DateTime|string date The date to format
 * @property string isoFormat
 * @property string format
 * @property string|null input_format
 *
 * @throws \Exception
 */
function smarty_modifier_print_date_iso_format($date, string $isoFormat = 'lll', string $format = 'c', string $input_format = null): string {
    if (is_string($date)) {
        if (empty($input_format)) {
            throw new Exception('When date is a string, input_format must be specified');
        }
        $date = DateTime::createFromFormat($input_format, $date);
    }

    return sprintf(
        '<span data-datetime="%s" title="%s">%s</span>',
        $date->format($format),
        $date->format($format),
        Carbon::parse($date->format('c'))->isoFormat($isoFormat)
    );
}
