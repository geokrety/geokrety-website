<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.movelink.php
 * Type:     modifier
 * Name:     movelink
 * Purpose:  outputs a move link
 * -------------------------------------------------------------
 */
function smarty_modifier_movelink(GeoKrety\Model\Move $move, ?string $textString = null, ?string $target = null): string {
    $text = is_null($textString) ? $move->geokret->gkid : $textString;
    $target_html = is_null($target) ? '' : ' target="'.$target.'"';

    return sprintf(
        '<a href="%s%s" title="%s"%s>%s</a>',
        GK_SITE_BASE_SERVER_URL,
        \Base::instance()->alias('geokret_details_paginate', ['gkid' => $move->geokret->gkid(), 'page' => $move->getMoveOnPage()], null, sprintf('log%d', $move->id)),
        _('View move details'),
        $target_html,
        smarty_modifier_escape($text),
    );
}
