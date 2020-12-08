<?php

use GeoKrety\Service\UserBanner;

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.userstatpic.php
 * Type:     modifier
 * Name:     userstatpic
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------
 */
function smarty_modifier_userstatpic(GeoKrety\Model\User $user) {
    return '<img id="statPic" src="'.UserBanner::get_banner_url($user).'" class="img-responsive center-block" title="'.sprintf(_('%s\'s statpic'), smarty_modifier_escape($user->username)).'" />';
}
