<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.url_picture.php
 * Type:     modifier
 * Name:     picture
 * Purpose:  outputs a picture from an url string
 * -------------------------------------------------------------.
 *
 * @throws \SmartyException
 */
function smarty_modifier_url_picture(?string $pictureUrl, ?string $thumbnailUrl = null, string $canvasDivId = null, string $caption = null, string $class = null): string {
    $template_string = <<<'EOT'
<div class="gallery">
    <figure class="{$class}">
        <div class="parent">
            <div class="image-container">
                {if !is_null($pictureUrl)}
                    {if !is_null($thumbnailUrl)}
                        <a class="picture-link" href="{$pictureUrl}">
                            <img src="{$thumbnailUrl}">
                        </a>
                    {else}
                        <img src="{$pictureUrl}">
                    {/if}
                {else if}
                    <svg class="picture-link" id="{$canvasDivId}"></svg>
                {/if}
            </div>
        </div>
        <figcaption>{$caption}</figcaption>
    </figure>
</div>
EOT;

    $smarty = GeoKrety\Service\Smarty::getSmarty();
    $smarty->assign('pictureUrl', $pictureUrl);
    $smarty->assign('thumbnailUrl', $thumbnailUrl);
    $smarty->assign('canvasDivId', $canvasDivId);
    $smarty->assign('caption', $caption ?? _('Altitude profile'));
    $smarty->assign('class', $class);
    $html = $smarty->fetch('string:'.$template_string);
    $smarty->clearAssign(['pictureUrl', 'thumbnailUrl', 'canvasDivId', 'caption', 'class']);

    return $html;
}
