{extends file='base.tpl'}

{block name=title}{t}Pictures gallery{/t}{/block}

{block name=content}
{include file='macros/pagination.tpl'}
<a class="anchor" id="gallery"></a>

<h2>🎒 {t}Pictures gallery{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-12">
        {include "blocks/pictures_gallery.tpl" pictures=$pictures}
    </div>
</div>

{/block}

{block name=javascript}
    {if $f3->get('SESSION.IS_LOGGED_IN')}
        // Bind modal
        {include 'js/dialogs/dialog_picture_actions.tpl.js'}
    {/if}
{/block}
