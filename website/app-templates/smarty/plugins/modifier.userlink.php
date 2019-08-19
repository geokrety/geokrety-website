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
function smarty_modifier_userlink($user, ?string $alternative_name = null) {
    $target_html = is_null($target) ? '' : ' target="'.$params['target'].'"';

    if (!$user->id) {
        $username = _('Anonymous');
        if (!is_null($alternative_name)) {
            $username = smarty_modifier_escape($alternative_name);
        }
        return '<em class="user-anonymous">'.$username.'</em>';
    }

    return '<a href="'.\Base::instance()->alias('user_details', 'userid='.$user->id).'" title="'.sprintf('View %s\'s profile', smarty_modifier_escape($user->username)).'"'.$target_html.'>'.smarty_modifier_escape($user->username).'</a>';
}
