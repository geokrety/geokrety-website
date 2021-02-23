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
    return '<img src="'.$badge->url.'" title="'.$badge->description.'" />';
}
