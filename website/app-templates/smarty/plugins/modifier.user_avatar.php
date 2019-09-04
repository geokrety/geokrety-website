<?php

use GeoKrety\Service\Libravatar;

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.user_avatar.php
 * Type:     modifier
 * Name:     user_avatar
 * Purpose:  outputs a user_avatar image via libravatar service
 * -------------------------------------------------------------
 */
function smarty_modifier_user_avatar(\GeoKrety\Model\User $user) {
    $identifier = $user->email ?: $user->username;

    $url = Libravatar::getUrl($identifier);
    $title = sprintf(_('%s\'s profile avatar'), $user->username);
    $size = 100;

    return '<img src="'.$url.'" width="'.$size.'" height="'.$size.'" title="'.smarty_modifier_escape($title).'" />';
}
