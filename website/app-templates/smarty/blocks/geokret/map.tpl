<div id="geokretyDetailsMapPanel" class="panel panel-default">
  <div id="geokretyDetailsMapPanelHeading" class="panel-heading">
    {t}Map{/t}
  </div>
  <div class="panel-body">
    {if $geokret->caches_count}
    <p>
      {t}Caption:{/t}
      <img src="{GK_CDN_ICONS_URL}/pins/red.png" alt="[Red flag]" width="12" height="20" /> = {t}start{/t}
      <img src="{GK_CDN_ICONS_URL}/pins/yellow.png" alt="[Yellow flag]" width="12" height="20" /> = {t}trip points{/t}
      <img src="{GK_CDN_ICONS_URL}/pins/green.png" alt="[Green flag]" width="12" height="20" /> = {t}recently seen{/t}
    </p>
    <div id="mapid" class="leaflet-container"></div>
    {else}
    {t}This geokret has not started yet{/t}
    {/if}
  </div>
</div>
