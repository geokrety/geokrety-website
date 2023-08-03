<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.array_string.php
 * Type:     modifier
 * Name:     array_string
 * Purpose:  Join array elements with a string (wrapper around `implode()`)
 * -------------------------------------------------------------.
 *
 * @throws \Exception
 */
function smarty_modifier_array_string(string $separator, array $array): string {
    return implode($separator, $array);
}
