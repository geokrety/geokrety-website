<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.gkicon.php
 * Type:     modifier
 * Name:     gkicon
 * Purpose:  outputs a geokrety icon based on gk type
 * -------------------------------------------------------------
 */
function smarty_modifier_gkicon(GeoKrety\Model\Geokret $geokret) {
    return '<img src="'.GK_CDN_IMAGES_URL.'/log-icons/'.$geokret->type->getTypeId().'/icon_25.jpg" class="img-fluid w-3" alt="'._('GK type icon').'" title="'.$geokret->type->getTypeString().'" data-gk-type="'.$geokret->type->getTypeId().'">';
}
