<?php

use GeoKrety\Model\User;
use GeoKrety\Service\Libravatar;

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';
require_once 'modifier.picture.php';
require_once 'modifier.url_picture.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.user_avatar.php
 * Type:     modifier
 * Name:     user_avatar
 * Purpose:  outputs a user_avatar image via libravatar service
 * -------------------------------------------------------------
 */
function smarty_modifier_user_avatar(User $user) {
    $identifier = $user->email ?: $user->username;
    $title = sprintf(_('%s\'s profile avatar'), $user->username);
    $size = 100;

    if (!$user->avatar) {
        $url = Libravatar::getUrl($identifier);

        return smarty_modifier_url_picture($url);
    }

    return smarty_modifier_picture($user->avatar, true);
}
