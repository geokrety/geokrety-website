{extends file='base.tpl'}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addCss(GK_CDN_STRENGTHIFY_CSS)}
{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_AJAX_JS)}
{\Assets::instance()->addJs(GK_CDN_STRENGTHIFY_JS)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL)}
{if GK_GOOGLE_RECAPTCHA_JS_URL}{\Assets::instance()->addJs(GK_GOOGLE_RECAPTCHA_JS_URL)}{/if}

{block name=content}
    <div class="row">
        <div class="col-xs-12 col-md-9">
            <div id="userAvatar" class="{if $user->isCurrentUser()}dropzone{/if}">
                {include file='blocks/user/details.tpl'}
                {include file='blocks/user/pictures.tpl'}
            </div>
            {include file='blocks/user/awards.tpl'}
            {include file='blocks/user/badges.tpl'}
        </div>
        <div class="col-xs-12 col-md-3">
            {include file='blocks/user/actions.tpl'}
            {if $user->isCurrentUser()}
                {include file='blocks/user/map_home.tpl'}
            {/if}
            {include file='blocks/user/statpic.tpl'}
        </div>
    </div>
{/block}

{block name=javascript}

    {if $user->isCurrentUser()}
        {include 'js/users/user_avatar_upload.tpl.js'}
    {/if}

    {if $user->isCurrentUser() && $user->hasHomeCoordinates()}
        {include file='js/_map_init.tpl.js'}
        var map = initializeMap();
        // Load GeoKrety near home position
        var bounds = map.getBounds();
        var geojsonLayer = new L.GeoJSON.AJAX("{GK_MAP_URL}/geojson?latTL="+bounds.getNorthWest().lat+"&lonTL="+bounds.getSouthEast().lng+"&latBR="+bounds.getSouthEast().lat+"&lonBR="+bounds.getNorthWest().lng+"&limit=500&json=1&daysFrom=0&daysTo=45");
        geojsonLayer.addTo(map);
    {/if}

    // Bind modal
    {include 'js/dialogs/dialog_user_details.tpl.js'}
    {include 'js/dialogs/dialog_contact_user.tpl.js'}
    {include 'js/dialogs/dialog_picture_actions.tpl.js'}
{/block}
