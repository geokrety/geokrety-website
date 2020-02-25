{function picture_base writable=false hideMainAvatarMedal=false url=null}
    <div class="gallery">
        <figure>
            <div{if isset($item)} id="{$item->key}"{/if} class="parent">
                <div class="image-container">
                    {if isset($item)}
                        {if is_null($item->uploaded_on_datetime)}
                            <img src="/assets/images/the-mole-grey.svg">
                            <span class="picture-message">{t}Picture is not yet ready{/t}</span>
                        {else}
                            <a class="picture-link" href="{$item->url}">
                                <img src="{$item->thumbnail_url}">
                            </a>
                        {/if}
                    {elseif !is_null($url)}
                        <img src="{$url}">
                    {else}
                        <img src="/assets/images/the-mole-grey.svg">
                    {/if}
                </div>
                <div class="overlay center-block">
                    {if !isset($item) && isset($writable) && $writable}
                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                    {/if}
                </div>
                {if !$hideMainAvatarMedal && isset($item) && $item->isGeokretMainAvatar()}
                    <div class="geokret-main-avatar"></div>
                {/if}
            </div>
            <figcaption>
                <p class="text-center picture-caption">
                    {if isset($item)}
                        {$item->caption}
                    {/if}
                </p>
                {if isset($link)}
                    <p class="text-center">
                        <!-- TODO: link to another item. GK/User/Move… -->
                        {*$link*}
                    </p>
                {/if}
            </figcaption>
            {if isset($item) && $item->isAuthor() && isset($writable) && $writable}
                <div class="pull-right">
                    <div class="btn-group geokret-picture-actions" role="group">
                        {if isset($item) && !$item->isGeokretMainAvatar()}
                            <button class="btn btn-primary btn-xs" title="{t}Define as main avatar{/t}"
                                    data-toggle="modal" data-target="#modal" data-type="geokret-avatar-define"
                                    data-id="{$item->key}">
                                ★
                            </button>
                        {/if}
                        <button class="btn btn-warning btn-xs" title="{t}Edit picture details{/t}" data-toggle="modal"
                                data-target="#modal" data-type="geokret-avatar-edit" data-id="{$item->key}">
                            {fa icon="pencil"}
                        </button>
                        <button class="btn btn-danger btn-xs" title="{t}Delete picture{/t}" data-toggle="modal"
                                data-target="#modal" data-type="geokret-avatar-delete" data-id="{$item->key}">
                            {fa icon="trash"}
                        </button>
                    </div>
                </div>
            {/if}
        </figure>
    </div>
{/function}

{function geokret_avatar_default writable=false hideMainAvatarMedal=false}
    {if isset($item)}
        {if isset($item) and is_a($item, '\GeoKrety\Model\Picture')}
            {call picture_base item=$item writable=$writable hideMainAvatarMedal=$hideMainAvatarMedal}
        {else}
            {* TODO: Raise error ifitem is not a Picture? *}
            {call picture_base writable=$writable}
        {/if}
    {else}
        {call picture_base}
    {/if}
{/function}
