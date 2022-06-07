{extends file='base.tpl'}

{block name=title}🛩️ {t username=$user->username}%1's Owned GeoKrety map{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS) && ''}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_CSS) && ''}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS) && ''}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS) && ''}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_AJAX_JS) && ''}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_MARKERCLUSTER_JS) && ''}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_SPIN_JS) && ''}

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
