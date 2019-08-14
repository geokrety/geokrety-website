<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.award.php
 * Type:     modifier
 * Name:     award
 * Purpose:  outputs a award icon
 * -------------------------------------------------------------
 */
function smarty_modifier_award(string $filename, string $count) {
    $url = GK_CDN_IMAGES_URL.'/medals/'.$filename;
    $title = sprintf(_('Award for %s GeoKrety'), $count);

    return '<img src="'.$url.'" title="'.$title.'" />';
}
