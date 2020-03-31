{extends file='base.tpl'}

{block name=content}
{include file='macros/pagination.tpl'}
<a class="anchor" id="gallery"></a>

<h2>🎒 {t username=$user->username}Pictures of %1's GeoKrety{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">
        {include "blocks/pictures_gallery.tpl" pictures=$pictures}
    </div>
    <div class="col-xs-12 col-md-3">
        {include file='blocks/user/actions.tpl'}
    </div>
</div>

{/block}

{block name=javascript}
    {if $user->isCurrentUser()}
        // Bind modal
        {include 'js/dialogs/dialog_picture_actions.tpl.js'}
    {/if}
{/block}
