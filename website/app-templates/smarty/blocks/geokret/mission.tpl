<div class="panel panel-default" id="geokretyMissionPanel">
  <div class="panel-heading">
    {t}Mission{/t}
    {if $geokret->isOwner()}
    <div class="btn-group pull-right" role="group">
      <a class="btn btn-warning btn-xs" href="{'geokret_edit'|alias}" title="{t}Edit GeoKret details{/t}">
        {fa icon="pencil"}
      </a>
    </div>
    <div class="clearfix"></div>
    {/if}
  </div>
  <div class="panel-body">
    {if not $geokret->mission}
    <em>{t}This GeoKret doesn't have a special mission…{/t}</em>
    {else}
    {$geokret->mission|markdown nofilter}
    {/if}
  </div>
</div>
