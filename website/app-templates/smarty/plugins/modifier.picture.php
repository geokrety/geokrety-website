<?php

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.picture.php
 * Type:     modifier
 * Name:     picture
 * Purpose:  outputs a picture
 * -------------------------------------------------------------
 */
function smarty_modifier_picture(\GeoKrety\Model\Picture $picture) {
    $iconUrl = GK_CDN_ICONS_URL.'/idcard.png';
    $alt = _('This is an image');
    $title = _('This is an image');

    return <<< EOT
<img src="$iconUrl" width="100" height="100" alt="$alt" title="$title" />

<div class="gallery">
    <figure>
        <div{if isset($id)} id="{$id}"{/if} class="parent">
            <div class="image-container">
                {if isset($url)}
                    <img src="{$url}">
                {else}
                    <img src="/assets/images/the-mole-grey.svg">
                {/if}
            </div>
            <div class="overlay center-block">
                {if isset($writable) && $writable}
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                {/if}
            </div>
        </div>
        <figcaption>
            <p class="text-center">
                {if isset($caption)}
                    {$caption}
                {/if}
            </p>
            {if isset($link)}
                <p class="text-center">
                    {$link}
                </p>
            {/if}
        </figcaption>
    </figure>
</div>



EOT;
}
