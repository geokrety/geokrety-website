<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.logicon.php
 * Type:     modifier
 * Name:     logicon
 * Purpose:  outputs a position icon
 * -------------------------------------------------------------
 */
function smarty_modifier_logicon(\GeoKrety\Model\Move $move) {
    $gkType = $move->geokret->type->getTypeId();

    return '<img src="'.GK_CDN_IMAGES_URL.'/log-icons/'.$gkType.'/'.$move->logtype->getLogTypeId().'.png" title="'.$move->id.': '.$move->logtype->getLogTypeString().'">';
}
