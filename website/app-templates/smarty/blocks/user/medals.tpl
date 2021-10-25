<div class="panel panel-default" id="userAwardsPanel">
  <div class="panel-heading">
    {t}Medals{/t}
  </div>
  <div class="panel-body">

    <p class="geokrety-created">
      {t escape=no user=$user->username|escape count=$geokretyOwned.count distance=$geokretyOwned.distance|distance}%1 has created <strong>%2</strong> GeoKrety, which travelled <strong>%3</strong>.{/t}
    </p>
    <span class="created-awards">
    {foreach $medalsGeoKretyOwned as $gkCount => $filename}
    {$filename|medal:$gkCount nofilter}
    {/foreach}
    </span>

    <p class="geokrety-moved">
      {t escape=no user=$user->username|escape count=$geokretyMoved.count distance=$geokretyMoved.distance|distance}%1 has moved <strong>%2</strong> GeoKrety on a total distance of <strong>%3</strong>.{/t}
    </p>
    <span class="move-awards">
    {foreach $medalsGeoKretyMoved as $gkCount => $filename}
    {$filename|medal:$gkCount nofilter}
    {/foreach}
    </span>

  </div>
</div>
