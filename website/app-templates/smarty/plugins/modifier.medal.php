<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.medal.php
 * Type:     modifier
 * Name:     medal
 * Purpose:  outputs a medal icon
 * -------------------------------------------------------------.
 */
function smarty_modifier_medal(string $filename, string $count) {
    $url = GK_CDN_IMAGES_URL.'/medals/'.$filename;
    $title = sprintf(_('Award for %s GeoKrety'), $count);

    return '<img src="'.$url.'" title="'.$title.'" />';
}
