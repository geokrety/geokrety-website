<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.gklink.php
 * Type:     modifier
 * Name:     gklink
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------
 */
function smarty_modifier_gklink(GeoKrety\Model\Geokret $geokret, ?string $textString = null, ?string $target = null): string {
    $text = is_null($textString) ? $geokret->name : $textString;
    $target_html = is_null($target) ? '' : ' target="'.$target.'"';

    return sprintf(
        '<a href="%s%s" data-gk-link="geokret" data-gk-id="%d" title="%s"%s>%s</a>',
        GK_SITE_BASE_SERVER_URL,
        \Base::instance()->alias('geokret_details', '@gkid='.$geokret->gkid),
        $geokret->id,
        sprintf(_('View %s\'s profile'), smarty_modifier_escape($geokret->name)),
        $target_html,
        smarty_modifier_escape($text),
    );
}
