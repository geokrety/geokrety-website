<?php

use GeoKrety\Service\Markdown;

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.markdown.php
 * Type:     modifier
 * Name:     markdown
 * Purpose:  markdown to html
 * -------------------------------------------------------------.
 */
function smarty_modifier_markdown(string $string, ?string $mode = 'html'): string {
    if ($mode === 'html') {
        return Markdown::toHtml($string);
    }

    return Markdown::toText($string);
}
