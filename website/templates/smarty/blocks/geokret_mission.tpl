<div class="panel panel-default">
  <div class="panel-heading">
    {t}Mission{/t}
  </div>
  <div class="panel-body">
    {if not $geokret_details->description}
    <em>{t}This GeoKret doesn't have a special mission…{/t}</em>
    {else}
    {$geokret_details->description}
    {/if}
  </div>
</div>
