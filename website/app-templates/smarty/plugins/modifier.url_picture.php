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
function smarty_modifier_url_picture(string $pictureUrl, string $thumbnailUrl = null): string {
    $template_string = <<<'EOT'
<div class="gallery">
    <figure>
        <div class="parent">
            <div class="image-container">
                {if !is_null($thumbnailUrl)}
                    <a class="picture-link" href="{$pictureUrl}">
                        <img src="{$thumbnailUrl}">
                    </a>
                {else}
                        <img src="{$pictureUrl}">
                {/if}
            </div>
        </div>
        <figcaption></figcaption>
    </figure>
</div>
EOT;

    $smarty = GeoKrety\Service\Smarty::getSmarty();
    $smarty->assign('pictureUrl', $pictureUrl);
    $smarty->assign('thumbnailUrl', $thumbnailUrl);
    $html = $smarty->display('string:'.$template_string);
    $smarty->clearAssign(['pictureUrl', 'thumbnailUrl']);

    return $html;
}
