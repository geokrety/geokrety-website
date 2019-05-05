<div class="panel panel-default">
  <div class="panel-heading">
    {t}Map{/t}
  </div>
  <div class="panel-body">
    {if $geokret_details->cachesCount}
    <p>
      {t}Legend:{/t}
      <img src="{$iconsUrl}/pins/red.png" alt="[Red flag]" width="12" height="20" /> = {t}start{/t}
      <img src="{$iconsUrl}/pins/yellow.png" alt="[Yellow flag]" width="12" height="20" /> = {t}trip points{/t}
      <img src="{$iconsUrl}/pins/green.png" alt="[Green flag]" width="12" height="20" /> = {t}recently seen{/t}
    </p>
    Download the track as:
    <a href="{$trip_gpx}">gpx</a> | <a href="{$trip_gpx}.gz">gpx.gz</a> | <a href="{$trip_csv}">csv.gz</a>
    <div id="mapid" class="leaflet-container"></div>
    {else}
    {t}This geokret has not started yet{/t}
    {/if}
  </div>
</div>
