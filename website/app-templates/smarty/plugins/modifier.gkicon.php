<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.gkicon.php
 * Type:     modifier
 * Name:     gkicon
 * Purpose:  outputs a geokrety icon based on gk type
 * -------------------------------------------------------------.
 */
function smarty_modifier_gkicon(GeoKrety\Model\Geokret $geokret): string {
    return sprintf(
        '<img src="%s/log-icons/%s/icon_25.jpg" class="img-fluid w-3" alt="%s" title="%s" data-gk-type="%s">',
        GK_CDN_IMAGES_URL,
        $geokret->type->getTypeId(),
        _('GK type icon'),
        $geokret->type->getTypeString(),
        $geokret->type->getTypeId()
    );
}
