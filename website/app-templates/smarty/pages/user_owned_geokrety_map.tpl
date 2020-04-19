{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_AJAX_JS)}

{block name=content}

<h2>🛩️ {t username=$user->username}%1's Owned GeoKrety map{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        <div id="mapid" class="leaflet-container"></div>

    </div>
    <div class="col-xs-12 col-md-3">
        {include file='blocks/user/actions.tpl'}

        <div class="panel panel-default">
            <div class="panel-heading">
                Show by
            </div>
            <div class="panel-body">
                <ul class="links">
                    <li>
                        <input type="radio" id="show-by-distance" name="show-by" value="distance" checked>
                        <label for="show-by-distance">📏 {t}Total distance{/t}</label>
                    </li>
                    <li>
                        <input type="radio" id="show-by-caches" name="show-by" value="caches">
                        <label for="show-by-caches"><img src="{GK_CDN_IMAGES_URL}/log-icons/2caches.png" title="{t}Caches visited count{/t}" /> {t}Total visited caches{/t}</label>
                    </li>
                    <li>
                        <input type="radio" id="show-by-days" name="show-by" value="days">
                        <label for="show-by-days">⏲️ {t}Last moved date{/t}</label>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

{/block}

{block name=javascript}
    {include file='js/users/owned_geokrety_map.tpl.js'}
{/block}
