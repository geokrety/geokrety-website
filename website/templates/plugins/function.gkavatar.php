<?php

require_once(SMARTY_PLUGINS_DIR . 'modifier.escape.php');

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.gkavatar.php
 * Type:     function
 * Name:     gkavatar
 * Purpose:  outputs an icon if the geokret has an avatar
 * -------------------------------------------------------------
 */
function smarty_function_gkavatar(array $params, Smarty_Internal_Template $template) {
    if (!in_array('gk', array_keys($params)) || empty($params['gk'])) {
        trigger_error("gkavatar: empty 'gk' parameter");

        return;
    }
    $gk = $params['gk'];
    if (!$gk->avatarId) {
        return;
    }

    $gkhex = gkid($gk->id);
    $iconUrl = CONFIG_CDN_ICONS.'/idcard.png';
    $title = smarty_modifier_escape(_('GeoKret has avatar'));
    $miniatureUrl = CONFIG_CDN_OBRAZKI_MALE.'/'.$gk->avatarFilename;
    $fullSizeUrl = CONFIG_CDN_OBRAZKI.'/'.$gk->avatarFilename;

    return <<< EOT
<a href="$fullSizeUrl" data-preview-image="$miniatureUrl">
  <img src="$iconUrl" width="14" height="10" alt="$title" />
</a>
EOT;
}
