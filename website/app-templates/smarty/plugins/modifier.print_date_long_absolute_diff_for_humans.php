<?php

use Carbon\Carbon;

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.print_date_long_absolute_diff_for_humans.php
 * Type:     modifier
 * Name:     print_date_long_absolute_diff_for_humans
 * Purpose:  outputs a date time as longAbsoluteDiffForHumans
 * Doc:      https://carbon.nesbot.com/docs/#api-humandiff
 * -------------------------------------------------------------.
 */
function smarty_modifier_print_date_long_absolute_diff_for_humans(DateTime $date, int $parts = 3): string {
    return Carbon::instance($date)->longAbsoluteDiffForHumans(['parts' => $parts]);
}
