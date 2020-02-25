{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_GEOKRETYFILTER_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_NOUISLIDER_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_FULLSCREEN_CSS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_MARKERCLUSTER_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_GEOKRETYFILTER_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_NOUISLIDER_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_SPIN_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_FULLSCREEN_JS)}

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
                    <dd>{t}Has moved since 90 days{/t}</dd>

                    <dt><img src="https://geokretymap.org/js/images/marker-icon-red.png"><span id="map-legend-red">0</span></dt>
                    <dd>{t}Has a known move date and hasn't moved since 90 days{/t}</dd>

                    <dt><img src="https://geokretymap.org/js/images/marker-icon-grey.png"><span id="map-legend-grey">0</span></dt>
                    <dd>{t}No known move date{/t}</dd>
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
