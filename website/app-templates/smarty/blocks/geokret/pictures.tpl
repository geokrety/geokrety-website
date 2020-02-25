{include file='macros/picture_geokret_avatar.tpl'}
<a class="anchor" id="gk-avatars-list"></a>
<div id="geokretPicturesList" class="panel panel-default{if !$avatars} hidden{/if} picturesList">
    <div class="panel-body">
        <div class="gallery">
            {if $avatars}
            {foreach from=$avatars item=picture}
                    {call picture_base writable=false item=$picture writable=true}
            {/foreach}
            {/if}
        </div>
    </div>
</div>
