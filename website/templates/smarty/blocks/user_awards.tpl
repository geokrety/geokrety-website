<div class="panel panel-default">
  <div class="panel-heading">
    Awards
  </div>
  <div class="panel-body">

    <p>
      {t user=$user->username count=$statsCreatedGeokrety.count distance=$statsCreatedGeokrety.distance}
        %1 has created <strong>%2</strong> GeoKrety, which travelled <strong>%3</strong> km.
      {/t}
    </p>
    {award_generator var='awardsCreated' count=$statsCreatedGeokrety.count}
    {foreach $awardsCreated as $title => $filename}
    {award title=$title file=$filename}
    {/foreach}

    <p>
      {t user=$user->username count=$statsMovedGeokrety.count distance=$statsMovedGeokrety.distance}
        %1 has moved <strong>%2</strong> GeoKrety on a total distance of <strong>%3</strong> km.
      {/t}
    </p>
    {award_generator var='awardsMoved' count=$statsMovedGeokrety.count}
    {foreach $awardsMoved as $title => $filename}
    {award title=$title file=$filename}
    {/foreach}

  </div>
</div>
