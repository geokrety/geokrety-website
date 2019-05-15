<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">{t}Define your observation area{/t}</h3>
  </div>
  <div class="panel-body">

    <form name="comment" action="/edit.php?co=latlon" method="post">
      <div class="modal-body">

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="inputCoordinates">{t}Home coordinates{/t}</label>
              <input type="text" class="form-control" id="inputCoordinates" name="coordinates" placeholder="{t}Home coordinates{/t}" value="{$smarty.post.coordinates}">
            </div>

            <p>
              <em>{t}Use the map to select a location or enter coordinates manually.{/t}</em>
              {fa icon="question-circle"}
              <a href="/help.php#acceptableformats">{t}Other acceptable lat/lon formats{/t}</a>
            </p>
            <pre class="small">
eg. 52.1534 21.0539
N 52° 09.204 E 021° 03.234
N 52° 9' 12.2400" E 21° 3' 14.0400
</pre>

            <div class="form-group">
              <label for="inputRadius">{t}Observation radius{/t}</label>
              <div class="input-group">
                <input type="number" class="form-control" id="inputRadius" name="radius" value="{$smarty.post.radius}" min="0" max="10">
                <span class="input-group-addon">km</span>
              </div>
            </div>

            <p>
              <em>{t}Range is limited to 10 km. Set it to 0 to disable the observation feature.{/t}</em>
            </p>

          </div>
          <div class="col-md-6">
            <div class="map-home">
              <figure class="">
                <div id="mapid" class="leaflet-container-200"></div>
              </figure>
              <figcaption>
                <p class="text-center"><small>{t}Center the map on the zone to observe{/t}</small></p>
              </figcaption>
            </div>
          </div>

        </div>
      </div>

      <div class="form-group">
        <a class="btn btn-default" href="{$user->geturl()}" title="{t}Back to user page{/t}">
          {t}Dismiss{/t}
        </a>
        <button type="submit" class="btn btn-primary">{t}Define{/t}</button>
      </div>

    </form>
  </div>
</div>
