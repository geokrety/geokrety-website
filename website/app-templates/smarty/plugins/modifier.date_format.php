<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.date_format.php
 * Type:     modifier
 * Name:     date_format
 * Purpose:  outputs a date time according to format
 * -------------------------------------------------------------
 */
function smarty_modifier_date_format(DateTime $date, string $format = 'c'): string {
    return $date->format('c');
}
