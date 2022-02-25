<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.logicon.php
 * Type:     modifier
 * Name:     logicon
 * Purpose:  outputs a position icon
 * -------------------------------------------------------------.
 */
function smarty_modifier_logicon(?GeoKrety\Model\Move $move, bool $showSmall = false) {
    if (is_null($move)) {
        return '';
    }
    $gkType = $move->geokret->type->getTypeId();

    $url = GK_SITE_BASE_SERVER_URL.\Base::instance()->alias('geokret_details', '@gkid='.$move->geokret->gkid);
    $img = '<img src="'.GK_CDN_IMAGES_URL.'/log-icons/'.$gkType.'/'.($showSmall ? '2' : '').$move->move_type->getLogTypeId().'.png" title="'.sprintf('%d: %s', $move->id, $move->move_type->getLogTypeString()).'" data-gk-move-type="'.$move->move_type->getLogTypeId().'" data-gk-move-id="'.$move->id.'">';

    return '<a href="'.$url.'#log'.$move->id.'">'.$img.'</a>';
}
