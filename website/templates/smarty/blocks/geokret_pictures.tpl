<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
        {t}Pictures{/t}
        {if $geokret_details->isOwner()}
        <div class="btn-group pull-right" role="group">
            <button class="btn btn-success btn-xs" title="{t}Upload a picture{/t}" data-toggle="modal" data-target="#modal" data-type="picture-upload" data-id="{$geokret_details->id}" data-picture-type="0">
                {fa icon="plus"}&nbsp;{fa icon="picture-o"}
            </button>
        </div>
        <div class="clearfix"></div>
        {/if}
    </div>
    <div class="panel-body">
        <div class="gallery">
            {foreach from=$geokret_pictures item=item}
            {picture item=$item skipLinkToEntity=true skipTags=false isOwner=$geokret_details->isOwner()}
            {foreachelse}
            <em>{t}No picture uploaded yet.{/t}</em>
            {/foreach}
        </div>
    </div>
</div>
