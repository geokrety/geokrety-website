<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.badge.php
 * Type:     modifier
 * Name:     badge
 * Purpose:  outputs a badge image
 * -------------------------------------------------------------
 */
function smarty_modifier_badge(GeoKrety\Model\Badge $badge) {
    $url = GK_CDN_IMAGES_URL.'/badges/'.$badge->filename;

    return '<img src="'.$url.'" title="'.$badge->description.'" />';
}
