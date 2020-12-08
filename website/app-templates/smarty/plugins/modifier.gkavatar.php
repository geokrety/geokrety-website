<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.gkavatar.php
 * Type:     modifier
 * Name:     gkavatar
 * Purpose:  outputs a geokrety icon based if the GK has an avatar
 * -------------------------------------------------------------
 */
function smarty_modifier_gkavatar(GeoKrety\Model\Geokret $geokret) {
    if (!$geokret->avatar) {
        return;
    }

    $iconUrl = GK_CDN_ICONS_URL.'/idcard.png';
    $alt = _('has avatar icon');
    $title = _('GeoKret has an avatar');

    return <<< EOT
<img src="$iconUrl" width="14" height="10" alt="$alt" title="$title" />
EOT;
}
