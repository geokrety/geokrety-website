<?php

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.statpictemplate.php
 * Type:     modifier
 * Name:     statpictemplate
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------.
 */
function smarty_modifier_statpictemplate(int $statpic_template): string {
    return sprintf(
        '<img src="/app-ui/statpics/templates/%d.png" class="img-responsive center-block"  alt="%s" />',
        $statpic_template,
        sprintf(_('User statistics banner: %s'), $statpic_template)
    );
}
