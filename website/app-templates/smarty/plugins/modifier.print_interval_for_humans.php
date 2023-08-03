<?php

use Carbon\CarbonInterval;

const PARAMETERS = ['years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds', 'microseconds'];

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.print_interval_for_humans.php
 * Type:     modifier
 * Name:     print_interval_for_humans
 * Purpose:  outputs an interval as human readable text
 * -------------------------------------------------------------.
 *
 * @throws \Exception
 */
function smarty_modifier_print_interval_for_humans(string $unit, int $value): string {
    if (!in_array($unit, PARAMETERS)) {
        throw new Exception('Invalid unit for print_interval_for_humans');
    }
    $years = $months = $weeks = $days = $hours = $minutes = $seconds = $microseconds = 0;
    $$unit = $value;
    $interval = CarbonInterval::create($years, $months, $weeks, $days, $hours, $minutes, $seconds, $microseconds);

    return $interval->cascade()->forHumans();
}
