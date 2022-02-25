<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.build.php
 * Type:     modifier
 * Name:     build
 * Purpose:  return the url of the given alias
 * -------------------------------------------------------------.
 */
function smarty_modifier_build($string): string {
    return \Base::instance()->build($string);
}
