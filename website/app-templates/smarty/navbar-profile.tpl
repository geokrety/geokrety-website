{if $f3->get('SESSION.IS_LOGGED_IN')}
{if $f3->get('SESSION.IS_ADMIN')}
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
        <li class="hidden-xs"><a id="navbar-profile-user-detail" href="{'user_details'|alias:sprintf('userid=%d', $f3->get('SESSION.CURRENT_USER'))}"><span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> {t}My profile{/t}</a></li>
        <li role="separator" class="divider hidden-xs"></li>
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
        <li><a id="navbar-profile-user-map" href="{'user_owned_map'|alias:sprintf('userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="map"} {t}Where are my GeoKrety?{/t}</a></li>
        <li role="separator" class="divider hidden-xs"></li>
        <li class="hidden-xs"><a id="navbar-profile-user-logout" href="{'logout'|alias}">{fa icon="sign-out"} {t}Sign out{/t}</a></li>
    </ul>
</li>
<li class="visible-xs-inline">
    <a href="{'logout'|alias}" class="red">{fa icon="sign-out"} {t}Sign out{/t}</a>
</li>
{else}
<li>
    <p class="navbar-btn">
        <div class="btn-group" role="group">
            {if GK_OPAUTH_FACEBOOK_CLIENT_ID !== false}
            <a id="navbar-facebookauth" href="/auth/facebook" class="btn btn-primary">
                {fa icon="facebook"}
            </a>
            {/if}
            {if GK_OPAUTH_GOOGLE_CLIENT_ID !== false}
            <a id="navbar-googleauth" href="/auth/google" class="btn btn-danger">
                {fa icon="google"}
            </a>
            {/if}
            <a id="navbar-profile-login" href="#" class="btn btn-info" data-toggle="modal" data-target="#modal" data-type="form-login">
                {fa icon="sign-in"} {t}Sign in{/t}
            </a>
        </div>
    </p>
</li>
<li><a id="navbar-profile-register" href="{'registration'|alias}">{fa icon="user-plus"} {t}Create account{/t}</a></li>
{/if}
