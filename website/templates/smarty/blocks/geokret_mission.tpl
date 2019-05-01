<div class="panel panel-default">
  <div class="panel-heading">
    {t}Mission{/t}
    {if $geokret_details->isOwner()}
    <div class="btn-group pull-right" role="group">
      <a class="btn btn-warning btn-xs" href="/edit.php?co=geokret&id={$geokret_details->id}" title="{t}Edit GeoKret details{/t}">
        {fa icon="pencil"}
      </a>
    </div>
    <div class="clearfix"></div>
    {/if}
  </div>
  <div class="panel-body">
    {if not $geokret_details->description}
    <em>{t}This GeoKret doesn't have a special mission…{/t}</em>
    {else}
    {$geokret_details->description}
    {/if}
  </div>
</div>
