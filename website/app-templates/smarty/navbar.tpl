<nav class="navbar-inverse navbar-fixed-top sidebarNavigation" data-sidebarClass="navbar-inverse">
    <div class="container-fluid">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed left-navbar-toggle" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-expanded="false">
                <span class="sr-only">{t}Toggle navigation{/t}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand visible-xs-inline" id="navbar-brand" href="{'home'|alias}">GeoKrety.org</a>

            <div class="pull-right hidden-sm hidden-md hidden-lg">
                {if $f3->get('SESSION.IS_LOGGED_IN')}
                <a href="{'move_create'|alias}" class="btn btn-success navbar-btn">
                    {t}Log a GeoKret{/t}
                </a>
                {else}
                <div class="btn-group navbar-btn" role="group">
                    {if GK_OPAUTH_FACEBOOK_CLIENT_ID !== false}
                        <a id="navbar-facebookauth-sm" href="/auth/facebook" class="btn btn-primary">
                            {fa icon="facebook"}
                        </a>
                    {/if}
                    {if GK_OPAUTH_GITHUB_CLIENT_ID !== false}
                        <a id="navbar-githubauth-sm" href="/auth/github" class="btn btn-black btn-github">
                            {fa icon="github"}
                        </a>
                    {/if}
                    {if GK_OPAUTH_GOOGLE_CLIENT_ID !== false}
                        <a id="navbar-googleauth-sm" href="/auth/google" class="btn btn-danger btn-google">
                            {fa icon="google"}
                        </a>
                    {/if}
                    <a id="navbar-profile-login-sm" href="{'login'|login_link}" class="btn btn-info" data-toggle="modal" data-target="#modal" data-type="form-login">
                        {fa icon="sign-in"} {t}Sign in{/t}
                    </a>
                </div>
                {/if}
                &nbsp;
            </div>
        </div>
        <div class="collapse navbar-collapse" id="bs-navbar-collapse">
            {include file='navbar-right.tpl'}
            {include file='navbar-left.tpl'}
        </div>

    </div>
</nav>
