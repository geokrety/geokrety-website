<?php

use GeoKrety\Model\Picture;

require_once SMARTY_PLUGINS_DIR.'modifier.escape.php';

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.picture.php
 * Type:     modifier
 * Name:     picture
 * Purpose:  outputs a picture
 * -------------------------------------------------------------.
 *
 * @throws \SmartyException
 */
function smarty_modifier_picture(?Picture $picture, ?bool $showActionsButtons = false, ?bool $showMainAvatarMedal = true, ?bool $allowSetAsMainAvatar = true, ?bool $showItemLink = false, ?bool $showPictureType = false) {
    if (is_null($picture)) {
        return '';
    }

    $template_string = <<<'EOT'
<div class="gallery" data-gk-type="picture" data-picture-type="{$picture->type->getTypeId()}" data-id="{$picture->id}">
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
            {if $showPictureType}
                {if $picture->isType(\Geokrety\PictureType::PICTURE_USER_AVATAR)}
                    <span class="type human"></span>
                {else if $picture->isType(\Geokrety\PictureType::PICTURE_GEOKRET_MOVE)}
                    <span class="type move"></span>
                {else if $picture->isType(\Geokrety\PictureType::PICTURE_GEOKRET_AVATAR)}
                    <span class="type geokret"></span>
                {/if}
            {/if}
        </div>
        <figcaption>
            <p class="text-center picture-caption" title="{$picture->caption}">{$picture->caption}</p>
            {if $showItemLink}
            <p class="text-center">
                {if $picture->isType(\Geokrety\PictureType::PICTURE_USER_AVATAR)}
                    {$picture->user|userlink nofilter}
                {else if $picture->isType(\Geokrety\PictureType::PICTURE_GEOKRET_MOVE)}
                    {$picture->move|movelink nofilter}
                {else if $picture->isType(\Geokrety\PictureType::PICTURE_GEOKRET_AVATAR)}
                    {$picture->geokret|gklink nofilter}
                {/if}
            </p>
            {/if}
        </figcaption>
        {if $showActionsButtons && ($picture->isAuthor() || $allowSetAsMainAvatar && $picture->hasPermissionOnParent() && !$picture->isMainAvatar()) && $picture->key}
            <div class="pull-right">
                <div class="pictures-actions pictures-actions-pull">
                    <div class="btn-group pictures-actions-buttons" role="group">
                        {if $allowSetAsMainAvatar && !$picture->isMainAvatar() && $picture->hasPermissionOnParent()}
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
    $smarty->assign('showActionsButtons', $showActionsButtons);
    $smarty->assign('allowSetAsMainAvatar', $allowSetAsMainAvatar);
    $smarty->assign('showPictureType', $showPictureType);
    $smarty->assign('showItemLink', $showItemLink);
    $html = $smarty->display('string:'.$template_string);
    $smarty->clearAssign(['picture', 'showMainAvatarMedal']);

    return $html;
}
