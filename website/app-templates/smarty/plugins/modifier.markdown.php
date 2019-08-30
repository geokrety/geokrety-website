<?php

use GeoKrety\Service\Markdown;

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
    return Markdown::toHtml($string);
}
