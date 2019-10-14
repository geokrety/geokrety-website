{if $f3->get('SESSION.IS_LOGGED_IN')}
{if IS_ADMIN}
<li><a id="navbar-profile-admin" href="{'admin_home'|alias}">{fa icon="support"} {t}Admin{/t}</a></li>
{/if}
<li>
    <a id="navbar-profile-profile" href="{'user_details'|alias:sprintf('userid=%d', $f3->get('SESSION.CURRENT_USER'))}">
        <span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> {t}My profile{/t}
    </a>
</li>
<li class="dropdown">
    <a id="navbar-profile-user" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
        {$current_user->username} <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        <li><a id="navbar-profile-user-detail" href="{'user_details'|alias:sprintf('userid=%d', $f3->get('SESSION.CURRENT_USER'))}"><span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> {t}My profile{/t}</a></li>
        <li role="separator" class="divider"></li>
        <li><a id="navbar-profile-user-inventory" href="{'user_inventory'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="briefcase"} {t}My inventory{/t}</a></li>
        <li><a id="navbar-profile-user-owned" href="{'user_owned'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="bolt"} {t}My GeoKrety{/t}</a></li>
        <li><a id="navbar-profile-user-watched" href="{'user_watched'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="binoculars"} {t}Watched GeoKrety{/t}</a></li>
        <li role="separator" class="divider"></li>
        <li><a id="navbar-profile-user-rmoves" href="{'user_recent_moves'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="plane"} {t}My recent moves{/t}</a></li>
        <li><a id="navbar-profile-user-omoves" href="{'user_owned_recent_moves'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="plane"} {t}Recent moves of my GeoKrety{/t}</a></li>
        <li role="separator" class="divider"></li>
        <li><a id="navbar-profile-user-pictures" href="{'user_pictures'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="picture-o"} {t}My photos{/t}</a></li>
        <li><a id="navbar-profile-user-opictures" href="{'user_owned_pictures'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="picture-o"} {t}Photos of my GeoKrety{/t}</a></li>
        <li role="separator" class="divider"></li>
        <li><a id="navbar-profile-user-map" href="{'geokrety_map'|alias:null:null:{\GeoKrety\Controller\Map::buildFragmentUserIdGeokrety($f3->get('SESSION.CURRENT_USER'))}}">{fa icon="map"} {t}Where are my GeoKrety?{/t}</a></li>
        <li role="separator" class="divider"></li>
        <li><a id="navbar-profile-user-logout" href="{'logout'|alias}">{fa icon="sign-out"} {t}Sign out{/t}</a></li>
    </ul>
</li>
{else}
<li>
    <p class="navbar-btn">
        <a id="navbar-profile-login" href="{login_link}" class="btn btn-primary btn-block">
            {fa icon="sign-in"} {t}Sign in{/t}
        </a>
    </p>
</li>
<li><a id="navbar-profile-register" href="{'registration'|alias}">{fa icon="user-plus"} {t}Create account{/t}</a></li>
{/if}
