<?php

use Carbon\Carbon;

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.print_date_expiration.php
 * Type:     modifier
 * Name:     print_date_expiration
 * Purpose:  outputs a date time as longRelativeDiffForHumans
 * Doc:      https://carbon.nesbot.com/docs/#api-humandiff
 * -------------------------------------------------------------
 */
function smarty_modifier_print_date_expiration(DateTime $date, string $format = 'c') {
    return Carbon::instance($date)->diffForHumans(['parts' => 3, 'join' => true]);
}
