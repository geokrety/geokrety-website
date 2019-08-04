<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.markdown.php
 * Type:     modifier
 * Name:     markdown
 * Purpose:  markdown to html
 * -------------------------------------------------------------
 */
function smarty_modifier_markdown($string) {
    $Parsedown = new \Parsedown();
    $html = $Parsedown->text($string);

    $HTMLPurifierconfig_conf = \HTMLPurifier_Config::createDefault();
    $HTMLPurifierconfig_conf->set('Cache.SerializerPath', TEMP_DIR_HTMLPURIFIER_CACHE);
    $HTMLPurifier = new \HTMLPurifier($HTMLPurifierconfig_conf);

    return $HTMLPurifier->purify($html);
}
