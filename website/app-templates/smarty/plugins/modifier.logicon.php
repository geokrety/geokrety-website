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
function smarty_modifier_logicon(?GeoKrety\Model\Move $move, bool $showSmall = false): string {
    if (is_null($move)) {
        return '';
    }
    $gkType = $move->geokret->type->getTypeId();

    $url = GK_SITE_BASE_SERVER_URL.\Base::instance()->alias('geokret_details', '@gkid='.$move->geokret->gkid);
    $img = sprintf(
        '<img src="%s/log-icons/%s/%s%s.png" title="%s" data-gk-move-type="%s" data-gk-move-id="%s">',
        GK_CDN_IMAGES_URL,
        $gkType,
        $showSmall ? '2' : '',
        $move->move_type->getLogTypeId(),
        sprintf('%d: %s', $move->id, $move->move_type->getLogTypeString()),
        $move->move_type->getLogTypeId(),
        $move->id
    );

    return sprintf(
        '<a href="%s#log%s">%s</a>',
        $url,
        $move->id,
        $img
    );
}
