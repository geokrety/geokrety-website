<a class="anchor" id="user-avatars-list"></a>
<div id="userPicturesList" class="panel panel-default{if !$user->pictures_count} hidden{/if} picturesList">
    <div class="panel-body">
        <div class="gallery">
            {if $user->pictures_count}
            {foreach from=$user->avatars item=picture}
                {$picture|picture nofilter}
            {/foreach}
            {/if}
        </div>
    </div>
</div>
