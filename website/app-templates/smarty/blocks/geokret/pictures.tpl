<a class="anchor" id="gk-avatars-list"></a>
<div id="geokretPicturesList" class="panel panel-default{if !$geokret->pictures_count} hidden{/if} picturesList">
    <div class="panel-body">
        <div class="gallery">
            {if $geokret->pictures_count}
            {foreach from=$geokret->avatars item=picture}
                    {$picture|picture nofilter}
            {/foreach}
            {/if}
        </div>
    </div>
</div>
