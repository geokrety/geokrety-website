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
    return \Formatter::toHtml($string);
}
