<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.userlink.php
 * Type:     modifier
 * Name:     userlink
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------
 */
function smarty_modifier_userlink(?GeoKrety\Model\User $user, ?string $alternative_name = null, ?string $target = null): string {
    $target_html = is_null($target) ? '' : ' target="'.$target.'"';
    if (is_null($user) || !$user->id) {
        $username = _('Anonymous');
        if (!is_null($alternative_name)) {
            $username = smarty_modifier_escape($alternative_name);
        }

        return '<em class="user-anonymous">'.$username.'</em>';
    }

    return sprintf(
        '<a href="%s%s" data-gk-link="user" data-gk-id="%d" title="%s"%s>%s</a>',
        GK_SITE_BASE_SERVER_URL,
        \Base::instance()->alias('user_details', 'userid='.$user->id),
        $user->id,
        sprintf('View %s\'s profile', smarty_modifier_escape($user->username)),
        $target_html,
        smarty_modifier_escape($user->username),
    );
}
