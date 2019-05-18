<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.userlink.php
 * Type:     function
 * Name:     userlink
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------
 */
function smarty_function_userlink(array $params, Smarty_Internal_Template $template) {
    if (!in_array('user', array_keys($params)) || empty($params['user'])) {
        trigger_error("userlink: missing 'user' parameter");

        return;
    }
    if (!is_a($params['user'], '\Geokrety\Domain\User')) {
        trigger_error("userlink: 'user' is not of type \Geokrety\Domain\User");

        return;
    }
    $user = $params['user'];

    if (!$user->id) {
        return '<em class="user-anonymous">'.smarty_modifier_escape($user->username).'</em>';
    }

    return '<a href="/mypage.php?userid='.$user->id.'" title="'.smarty_modifier_escape($user->username).'">'.smarty_modifier_escape($user->username).'</a>';
}
