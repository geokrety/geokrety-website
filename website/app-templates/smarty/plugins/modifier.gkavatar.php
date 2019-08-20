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
function smarty_modifier_gkavatar(\GeoKrety\Model\Geokret $geokret) {
    if (!$geokret->avatar) {
        return;
    }

    $gkhex = $geokret->gkid;
    $iconUrl = GK_CDN_ICONS_URL.'/idcard.png';
    $alt = _('has avatar icon');
    $title = _('GeoKret has an avatar');
    // $miniatureUrl = CONFIG_CDN_OBRAZKI_MALE.'/'.$gk->avatarFilename;
    // $fullSizeUrl = CONFIG_CDN_OBRAZKI.'/'.$gk->avatarFilename;

    return <<< EOT
<img src="$iconUrl" width="14" height="10" alt="$alt" title="$title" />
EOT;
}
