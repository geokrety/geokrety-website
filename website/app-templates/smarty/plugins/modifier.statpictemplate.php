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
function smarty_modifier_statpictemplate(int $statpic_template) {
    return '<img src="/app-ui/statpics/templates/'.$statpic_template.'.png" class="img-responsive center-block"  alt="'.sprintf(_('User statistics banner: %s'), $statpic_template).'" />';
}
