<ol class="breadcrumb">
    <li><a href="/">{t}Home{/t}</a></li>
    <li class="active">{t}GeoKrety Map{/t}</li>
</ol>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">{t}Geokrety interactive map{/t}</div>
            <div class="panel-body">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">

        <div class="panel panel-default">
            <div class="panel-heading">{t}Legend{/t}</div>
            <div class="panel-body">
                <dl id="map-legend">
                    <dt><img src="https://geokretymap.org/js/images/marker-icon.png"><span id="map-legend-blue">0</span></dt>
                    <dd>Has moved since 90 days</dd>

                    <dt><img src="https://geokretymap.org/js/images/marker-icon-red.png"><span id="map-legend-red">0</span></dt>
                    <dd>Has a known move date and hasn't moved since 90 days</dd>

                    <dt><img src="https://geokretymap.org/js/images/marker-icon-grey.png"><span id="map-legend-grey">0</span></dt>
                    <dd>No known move date</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-body">
                <p>{t}Results are limited to the first 500 responses.{/t}</p>
                <p>{t escape=no id="map-legend-total" total="500"}Currently displayed GeoKrety <span id="%1">0</span> on %2{/t}</p>
            </div>
        </div>
    </div>

</div>