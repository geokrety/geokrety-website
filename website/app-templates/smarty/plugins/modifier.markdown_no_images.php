<?php

use GeoKrety\Service\MarkdownNoImages;

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.markdown_no_images.php
 * Type:     modifier
 * Name:     markdown
 * Purpose:  markdown to html
 * -------------------------------------------------------------.
 */
function smarty_modifier_markdown_no_images(?string $string, ?string $mode = 'html'): string {
    if (is_null($string)) {
        return '';
    }
    if ($mode === 'html') {
        return MarkdownNoImages::toHtml($string);
    }

    return MarkdownNoImages::toText($string);
}
