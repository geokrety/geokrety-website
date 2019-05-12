<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingOne">
    {t}Pictures{/t}
    {if $isGeokretOwner}
    <div class="btn-group pull-right" role="group">
      <a class="btn btn-success btn-xs" href="/imgup.php?typ=0&id={$geokret_details->id}" title="{t}Upload a picture{/t}">
        {fa icon="picture-o"}
      </a>
    </div>
    <div class="clearfix"></div>
    {/if}
  </div>
  <div class="panel-body">
    <div class="gallery">
      {foreach from=$geokret_pictures item=item}
      {picture item=$item skipLinkToEntity=true skipTags=true isGeokretOwner=$isGeokretOwner}
      {foreachelse}
      <em>{t}No picture uploaded yet.{/t}</em>
      {/foreach}
    </div>
  </div>
</div>
