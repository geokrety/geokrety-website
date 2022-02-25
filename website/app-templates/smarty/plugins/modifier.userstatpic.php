<?php

use GeoKrety\Service\UserBanner;

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.userstatpic.php
 * Type:     modifier
 * Name:     userstatpic
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------.
 */
function smarty_modifier_userstatpic(GeoKrety\Model\User $user): string {
    return sprintf(
        '<img id="statPic" src="%s" class="img-responsive center-block" title="%s" />',
        UserBanner::get_banner_url($user),
        sprintf(_('%s\'s statpic'),
            smarty_modifier_escape($user->username))
    );
}
