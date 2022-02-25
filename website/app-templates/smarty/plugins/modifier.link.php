<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.link.php
 * Type:     modifier
 * Name:     link
 * Purpose:  outputs an html link
 * -------------------------------------------------------------.
 */
function smarty_modifier_link(string $url, ?string $textString = null, ?string $target = null): string {
    $text = is_null($textString) ? $url : smarty_modifier_escape($textString);
    $target_html = is_null($target) ? '' : ' target="'.$target.'"';

    return sprintf(
        '<a href="%s" title="%s"%s>%s</a>',
        $url,
        $text,
        $target_html,
        $text,
    );
}
