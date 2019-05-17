{function img width=100 height=100}{* filename="" *}
<a href="{$avatarUrl}/{$filename}">
    <img src="{$avatarMinUrl}/{$filename}" width="{$width}" height="{$height}" data-preview-image="{$avatarUrl}/{$filename}" />
</a>
{/function}

{function picture skipLinkToEntity=false skipTags=false isOwner=false skipButtons=false}{* item="" *}
<figure>
    <div class="parent">
        <div class="image-container{if $item->isAvatar()} is-avatar{/if}">
            {call img filename=$item->filename}
            {if not $skipTags}
            {if isset($item->country) and $item->country}{country_flag country=$item->country}{/if}
            {if $item->type == 0}<span class="type gk" title="{t}A GeoKret avatar{/t}"></span>
            {elseif $item->type == 1}<span class="type plane" title="{t}A move picture{/t}"></span>
            {elseif $item->type == 2}<span class="type human" title="{t}User's avatar{/t}"></span>
            {/if}
            {/if}
        </div>
    </div>
    <figcaption>
        <p class="text-center">
            {if $item->legend}
            <small title="{$item->legend}">{$item->legend|truncate:30:'…'}</small>
            {else}
            &nbsp;
            {/if}
        </p>
        {if not $skipLinkToEntity}
        <p class="text-center">
            {if $item->type == 0}{* GK *}
            {gklink gk=$item->geokret()}
            {else if $item->type == 1}{* MOVE *}
            {triplink trip=$item->trip()}
            {else if $item->type == 2}{* USER *}
            {userlink user=$item->author()}
            {else}
            {t}Unknown type{/t}
            {/if}
        </p>
        {elseif not $skipButtons and $isLoggedIn and ($isOwner or $currentUser == $item->userId)}
        <div class="btn-group pull-right" role="group">
            {if not $item->isAvatar() and $item->geokretId}
            <button type="button" class="btn btn-success btn-xs" title="{t}Set as avatar{/t}" data-toggle="modal" data-target="#modal" data-type="picture-set-avatar" data-pictureid="{$item->id}" data-geokretid="{$item->geokretId}">
                {fa icon="id-card"}
            </button>
            {/if}
            <button class="btn btn-warning btn-xs" title="{t}Edit picture{/t}" data-toggle="modal" data-target="#modal" data-type="picture-edit" data-id="{$item->tripId}" data-picture-type="{$item->type}" data-picture-id="{$item->id}">
                {fa icon="pencil"}
            </button>
            <button type="button" class="btn btn-danger btn-xs" title="{t}Delete picture{/t}" data-toggle="modal" data-target="#modal" data-type="picture-delete" data-picture-id="{$item->id}">
                {fa icon="trash"}
            </button>
        </div>
        {/if}
    </figcaption>
</figure>
{/function}

{function pictureDefault overlayAdd=false isOwner=false skipButtons=false}{* item="" *}
<figure>
    <div class="parent"{if not $skipButtons and $isOwner} data-toggle="modal" data-target="#modal" data-type="picture-upload" data-id="{$id}" data-picture-type="{$pictureType}" data-is-avatar="on"{/if}>
        <div class="image-container">
            <img src="{$imagesUrl}/the-mole-grey.svg" class="center-block" />
        </div>
        {if not $skipButtons and $isOwner}
        <div class="overlay center-block">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
        </div>
        {/if}
    </div>
    <figcaption>
        {if not $skipButtons and $isOwner}
        <p class="text-center" title="{t}Add an avatar{/t}"><small><em>{t}Add an avatar{/t}</em></small></p>
        <div class="btn-group pull-right" role="group">
            <button class="btn btn-success btn-xs" title="{t}Upload an avatar{/t}" data-toggle="modal" data-target="#modal" data-type="picture-upload" data-id="{$id}" data-picture-type="{$pictureType}" data-is-avatar="on">
                {fa icon="plus"}&nbsp;{fa icon="picture-o"}
            </button>
        </div>
        {else}
        <p class="text-center"><small><em>{t}No avatar{/t}</em></small></p>
        {/if}
    </figcaption>
</figure>
{/function}

{function pictureOrDefault skipLinkToEntity=false skipTags=false isOwner=false skipButtons=false pictureType=0 id=0}{* item="" *}
{if isset($item) && $item && $item->filename}
{picture item=$item skipLinkToEntity=$skipLinkToEntity skipTags=$skipTags isOwner=$isOwner skipButtons=$skipButtons}
{else}
{pictureDefault isOwner=$isOwner pictureType=$pictureType id=$id}
{/if}
{/function}

{function altitudeProfile}{* gk_id="" *}
<figure>
    <div class="parent">
        {if isset($geokret_altitude_profile)}
        <img src="{$imagesUrl}/wykresy/{$gk_id}-m.png" />
        {else}
        {fa icon="line-chart" size="4x"}
        {/if}
    </div>
    <figcaption>
        <p class="text-center">
            <small><em>{t}Altitude profile{/t}</em></small>
            <a href="/help.php#altitude" title="{t}How is this computed?{/t}">{fa icon="question-circle"}</a>
        </p>
    </figcaption>
</figure>
{/function}
