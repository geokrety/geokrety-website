{extends file='base.tpl'}

{block name=css}
<link rel="stylesheet" href="{GK_CDN_LEAFLET_CSS}">
<link rel="stylesheet" href="{GK_CDN_LEAFLET_MARKERCLUSTER_CSS}">
<link rel="stylesheet" href="{GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS}">
<link rel="stylesheet" href="{GK_CDN_LEAFLET_GEOKRETYFILTER_CSS}">
<link rel="stylesheet" href="{GK_CDN_LEAFLET_NOUISLIDER_CSS}">
<link rel="stylesheet" href="{GK_CDN_LEAFLET_FULLSCREEN_CSS}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_LEAFLET_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LEAFLET_MARKERCLUSTER_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LEAFLET_GEOKRETYFILTER_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LEAFLET_NOUISLIDER_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LEAFLET_SPIN_JS}"></script>
<script type="text/javascript" src="{GK_CDN_LEAFLET_FULLSCREEN_JS}"></script>
{/block}

{block name=content}
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">{t}GeoKrety interactive map{/t}</div>
            <div class="panel-body">
                <div id="mapid" class="leaflet-container"></div>
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
{/block}

{block name=javascript}
{include file='js/map.tpl.js'}
{/block}
