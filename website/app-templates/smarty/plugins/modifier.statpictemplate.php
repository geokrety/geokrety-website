<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.statpictemplate.php
 * Type:     modifier
 * Name:     statpictemplate
 * Purpose:  outputs a geokret link
 * -------------------------------------------------------------
 */
function smarty_modifier_statpictemplate(int $statpic_template) {
    return '<img src="'.GK_CDN_IMAGES_URL.'/statpics/wzory/'.$statpic_template.'.png" class="img-responsive center-block"  alt="{t id=$statpic}User statistics banner: %1{/t}" />';
}
