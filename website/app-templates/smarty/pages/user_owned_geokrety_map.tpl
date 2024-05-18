{extends file='base.tpl'}

{block name=title}🛩️ {t username=$user->username}%1's Owned GeoKrety map{/t}{/block}

{\GeoKrety\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS) && ''}
{\GeoKrety\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_CSS) && ''}
{\GeoKrety\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LEAFLET_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LEAFLET_AJAX_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LEAFLET_MARKERCLUSTER_JS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_LEAFLET_SPIN_JS) && ''}

{block name=content}

<h2>🛩️ {t username=$user->username}%1's Owned GeoKrety map{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        <div id="mapid" class="leaflet-container-600"></div>

    </div>
    <div class="col-xs-12 col-md-3">
        {include file='blocks/user/actions.tpl'}
        {include file='elements/map_caption.tpl'}
    </div>
</div>

{/block}

{block name=javascript}
    {include file='js/users/owned_geokrety_map.tpl.js'}
{/block}
