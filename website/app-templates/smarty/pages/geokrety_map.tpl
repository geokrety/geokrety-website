{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_AJAX_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_MARKERCLUSTER_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_SPIN_JS)}

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
{/block}

{block name=javascript}
{include file='js/geokrety_map.tpl.js'}
{/block}
