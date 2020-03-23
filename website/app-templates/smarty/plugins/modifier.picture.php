<?php

use GeoKrety\Model\Picture;

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
function smarty_modifier_picture(Picture $picture, ?bool $showMainAvatarMedal = false) {
    $template_string = <<<'EOT'
<div class="gallery">
    <figure>
        <div id="{$picture->key}" class="parent">
            <div class="image-container">
                {if !$picture->isUploaded()}
                    <img src="/assets/images/the-mole-grey.svg">
                    <span class="picture-message">{t}Picture is not yet ready{/t}</span>
                {else}
                    <a class="picture-link" href="{$picture->url}">
                        <img src="{$picture->thumbnail_url}">
                    </a>
                {/if}
            </div>
            {if $showMainAvatarMedal && $picture->isMainAvatar()}
                <div class="picture-is-main-avatar" data-toggle="tooltip" title="{t}This is the main avatar{/t}"></div>
            {/if}
        </div>
        <figcaption>
            <p class="text-center picture-caption">
                {$picture->caption}
            </p>
            <p class="text-center">
                <!-- TODO: link to another item. GK/User/Move… -->
            </p>
        </figcaption>
        {if $showMainAvatarMedal && ($picture->isAuthor() || $picture->hasPermissionOnParent() && !$picture->isMainAvatar())}
            <div class="pull-right">
                <div class="pictures-actions pictures-actions-pull">
                    <div class="btn-group pictures-actions-buttons" role="group">
                        {if !$picture->isMainAvatar() && $picture->hasPermissionOnParent()}
                            <button class="btn btn-primary btn-xs" title="{t}Define as main avatar{/t}"
                                    data-toggle="modal" data-target="#modal" data-type="define-as-main-avatar"
                                    data-id="{$picture->key}">
                                ★
                            </button>
                        {/if}
                        {if $picture->isAuthor()}
                        <button class="btn btn-warning btn-xs" title="{t}Edit picture details{/t}"
                                data-toggle="modal" data-target="#modal" data-type="picture-edit" data-id="{$picture->key}">
                            {fa icon="pencil"}
                        </button>
                        <button class="btn btn-danger btn-xs" title="{t}Delete picture{/t}"
                                data-toggle="modal" data-target="#modal" data-type="picture-delete" data-id="{$picture->key}">
                            {fa icon="trash"}
                        </button>
                        {/if}
                    </div>
                </div>
            </div>
        {/if}
    </figure>
</div>
EOT;

    $smarty = GeoKrety\Service\Smarty::getSmarty();
    $smarty->assign('picture', $picture);
    $smarty->assign('showMainAvatarMedal', $showMainAvatarMedal);
    $html = $smarty->display('string:'.$template_string);
    $smarty->clearAssign(['picture', 'showMainAvatarMedal']);

    return $html;
}
