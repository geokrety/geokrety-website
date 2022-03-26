{extends file='base.tpl'}

{block name=title}{t username=$user->username}%1's user profile{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_LEAFLET_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_CSS)}
{\Assets::instance()->addCss(GK_CDN_LEAFLET_MARKERCLUSTER_DEFAULT_CSS)}
{\Assets::instance()->addCss(GK_CDN_STRENGTHIFY_CSS)}
{\Assets::instance()->addCss(GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_AJAX_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_MARKERCLUSTER_JS)}
{\Assets::instance()->addJs(GK_CDN_LEAFLET_SPIN_JS)}
{\Assets::instance()->addJs(GK_CDN_STRENGTHIFY_JS)}
{\Assets::instance()->addJs(GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL)}
{include file='macros/recaptcha.tpl'}

{block name=content}
    <div class="row">
        <div class="col-xs-12 col-md-9">
            <div id="userAvatar" class="{if $user->isCurrentUser()}dropzone{/if}">
                {include file='blocks/user/details.tpl'}
                {include file='blocks/user/pictures.tpl'}
            </div>
            {include file='blocks/user/medals.tpl'}
            {include file='blocks/user/awards.tpl'}
        </div>
        <div class="col-xs-12 col-md-3">
            {include file='blocks/user/actions.tpl'}
            {if !$user->isConnectedWithProvider()}
            {include file='blocks/user/oauth.tpl'}
            {/if}
            {if $user->isCurrentUser()}
                {include file='blocks/user/map_home.tpl'}
            {/if}
            {include file='blocks/user/statpic.tpl'}
            {if $user->isConnectedWithProvider()}
            {include file='blocks/user/oauth.tpl'}
            {/if}
            {include file='blocks/user/danger_zone.tpl'}
        </div>
    </div>
{/block}

{block name=javascript}

    {if $user->isCurrentUser()}
        {include 'js/users/user_avatar_upload.tpl.js'}
    {/if}

    {if $user->isCurrentUser() && $user->hasHomeCoordinates()}
        {include file='js/users/geokrety_near_home_map.tpl.js'}
        {if GK_DEVEL}
        {* used by Tests-qa in Robot  Framework *}
        $("#mapid").data({ map: map });
        {/if}
    {/if}

    // Bind modal
    {include 'js/dialogs/dialog_user_details.tpl.js'}
    {include 'js/dialogs/dialog_contact_user.tpl.js'}
    {include 'js/dialogs/dialog_picture_actions.tpl.js'}
    {include 'js/dialogs/dialog_oauth_disconnect.tpl.js'}
{/block}
