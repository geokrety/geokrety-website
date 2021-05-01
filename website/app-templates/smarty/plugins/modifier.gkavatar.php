<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.gkavatar.php
 * Type:     modifier
 * Name:     gkavatar
 * Purpose:  outputs a geokrety icon based if the GK has an avatar
 * -------------------------------------------------------------
 */
function smarty_modifier_gkavatar(GeoKrety\Model\Geokret $geokret): string {
    if (!$geokret->avatar) {
        return '';
    }

    $iconUrl = GK_CDN_ICONS_URL.'/idcard.png';
    $alt = _('has avatar icon');
    $title = _('GeoKret has an avatar');

    $html = <<< EOT
<a class="has-gk-avatar" href="{$geokret->avatar->url}" title="%s">
    <img src="$iconUrl" class="img-fluid w-3" width="14" height="10" alt="$alt" title="$title" />
</a>
EOT;
    return sprintf(
        $html,
        smarty_modifier_escape(sprintf(_('GeoKret "%s" by %s'), $geokret->name, $geokret->avatar->author->username)),
    );
}
