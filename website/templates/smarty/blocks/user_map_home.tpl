<div class="panel panel-default">
  <div class="panel-body">
    <div class="map-home">
      <figure class="">
        {if $user->hasCoordinates()}
        <div id="mapid" class="leaflet-container"></div>
        {else}
        <div class="leaflet-container text-center">
          <p>
            <em>No home coordinates have been defined.</em>
          </p>
          <br />
          <a class="btn btn-warning btn-xs" href="/edit.php?co=latlon" title="{t}Update home coordinates and obervation area{/t}">
            {fa icon="pencil"} {t}Define your home coordinates{/t}
          </a>
        </div>
        {/if}
      </figure>
      <figcaption>
        <p class="text-center"><small>{t}GeoKrety near home{/t}</small></p>
        <a class="btn btn-warning btn-xs pull-right" href="/edit.php?co=latlon" title="{t}Update home coordinates and obervation area{/t}">
          {fa icon="pencil"}
        </a>
      </figcaption>
    </div>
  </div>
</div>
