<?php

use \Geokrety\GeokretyType;
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.gkicon.php
 * Type:     modifier
 * Name:     gkicon
 * Purpose:  outputs a geokrety icon based on gk type
 * -------------------------------------------------------------
 */
function smarty_modifier_gkicon(\GeoKrety\Model\Geokret $geokret) {
    return '<img src="'.GK_CDN_IMAGES_URL.'/log-icons/'.$geokret->type->getTypeId().'/icon_25.jpg" alt="{t}GK type icon{/t}" title="'.$geokret->type->getTypeId().'">';
}
