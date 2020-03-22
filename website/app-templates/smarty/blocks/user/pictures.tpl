<a class="anchor" id="user-avatars-list"></a>
<div id="userPicturesList" class="panel panel-default{if !$avatars} hidden{/if} picturesList">
    <div class="panel-body">
        <div class="gallery">
            {if $avatars}
            {foreach from=$avatars item=picture}
                {$picture|picture nofilter}
            {/foreach}
            {/if}
        </div>
    </div>
</div>
