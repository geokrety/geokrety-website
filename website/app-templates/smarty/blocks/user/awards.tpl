<div class="panel panel-default">
  <div class="panel-heading">
    {t}Awards{/t}
  </div>
  <div class="panel-body">

    <p>
      {t escape=no user=$user->username|escape count=$geokretyOwned.count distance=$geokretyOwned.distance}
        %1 has created <strong>%2</strong> GeoKrety, which travelled <strong>%3</strong> km.
      {/t}
    </p>
    {foreach $awardsGeoKretyOwned as $gkCount => $filename}
    {$filename|award:$gkCount nofilter}
    {/foreach}

    <p>
      {t escape=no user=$user->username|escape count=$geokretyMoved.count distance=$geokretyMoved.distance}
        %1 has moved <strong>%2</strong> GeoKrety on a total distance of <strong>%3</strong> km.
      {/t}
    </p>
    {foreach $awardsGeoKretyMoved as $gkCount => $filename}
    {$filename|award:$gkCount nofilter}
    {/foreach}

  </div>
</div>
