<ul class="nav navbar-nav">
    <li><a id="navbar-home" href="{'home'|alias}" title="{t}Home{/t}"><span class="glyphicon glyphicon-home" aria-hidden="true"></span><span class="visible-xs-inline"> {t}Home{/t}</span></a></li>
    <li><a id="navbar-new" href="{'news_list'|alias}" title="{t}News{/t}">{fa icon="newspaper-o"}<span class="visible-xs-inline"> {t}News{/t}</span></a></li>
    <li>
        <p class="navbar-btn">
            <a id="navbar-move" href="{'move_create'|alias}" class="btn btn-success btn-block"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> {t}Log a GeoKret{/t}</a>
        </p>
    </li>

    <li class="dropdown">
        <a id="navbar-actions" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{fa icon="cogs"} {t}Actions{/t} <span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a id="navbar-actions-move" href="{'move_create'|alias}"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> {t}Log a GeoKret{/t}</a></li>
            {if $f3->get('SESSION.IS_LOGGED_IN')}
                <li><a id="navbar-actions-create" href="{'geokret_create'|alias}"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> {t}Create a new GeoKret{/t}</a></li>
                <li><a id="navbar-actions-claim" href="{'geokret_claim'|alias}"><span class="glyphicon glyphicon-heart" aria-hidden="true"></span> {t}Claim a GeoKret{/t}</a></li>
            {/if}
            <li role="separator" class="divider"></li>
            <li><a id="navbar-actions-search" href="{'advanced_search'|alias}"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> {t}Advanced search{/t}</a></li>
            <li><a id="navbar-actions-gallery" href="{'photo_gallery'|alias}"><span class="glyphicon glyphicon-picture" aria-hidden="true"></span> {t}Photo gallery{/t}</a></li>
            <li><a id="navbar-new" href="{'geokrety_labels'|alias}">{fa icon="tag"} {t}Mass labelling{/t}</a></li>
        </ul>
    </li>
    <li><a id="navbar-map" href="{'geokrety_map'|alias}" title="{t}GeoKrety Map{/t}">{fa icon="map"}<span class="visible-xs-inline"> {t}GeoKrety Map{/t}</span></a></li>
</ul>

<ul class="nav navbar-nav">
    <li class="dropdown">
        <a id="navbar-lang" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <img src="{GK_CDN_ICONS_URL}/language.svg" width="14" /><span class="visible-xs-inline" title="{t}Currently selected language{/t}"> {\Multilang::instance()->current|language:true}</span>
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            {foreach $languages as $code => $lang}
                <li>
                    <a id="navbar-lang-{$code}" href="{\Multilang::instance()->alias($f3->get('ALIAS'), $f3->get('PARAMS'), $code)}{if $f3->exists('GET')}?{http_build_query($f3->get('GET')) nofilter}{/if}" class="{if \Multilang::instance()->current == $code}bold{/if}">{$lang}</a>
                </li>
            {/foreach}
        </ul>
    </li>

    <li class="dropdown">
        <a id="navbar-help" title="{t}Help{/t}" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{fa icon="support"}<span class="visible-xs-inline"> {t}Help{/t}</span><span class="caret"></span></a>
        <ul class="dropdown-menu">
            <li><a id="navbar-help-help" href="{'help'|alias}">{fa icon="support"} {t}Help{/t}</a></li>
            {*                        <li><a id="navbar-help-holes" href="{'mole_holes'|alias}">{fa icon="bed"} {t}Moleholes and GK hotels{/t}</a></li>*}
            <li><a id="navbar-help-terms" href="{'terms_of_use'|alias}">{fa icon="legal"} {t}Terms of use{/t}</a></li>
            <li><a id="navbar-help-press" href="{'press_corner'|alias}">{fa icon="newspaper-o"} {t}Press corner{/t}</a></li>
            <li role="separator" class="divider"></li>
            <li><a id="navbar-help-api" href="{'help_api'|alias}">{fa icon="cog"} {t}GK interface / API{/t}</a></li>
            <li role="separator" class="divider"></li>
            <li><a id="navbar-stats" href="{'statistics_waypoints'|alias}">{fa icon="bar-chart"} {t}Waypoints synchronization status{/t}</a></li>
            <li><a id="navbar-ranking" href="{'statistics_awards_ranking_index'|alias}">{fa icon="line-chart"} {t}Yearly rankings{/t}</a></li>
            <li role="separator" class="divider"></li>
            <li><a id="navbar-downloads" href="{'downloads'|alias}">{fa icon="download"} {t}Downloads{/t}</a></li>
            <li><a id="navbar-toolbox" href="{'geokrety_toolbox'|alias}">{fa icon="cog"} {t}GeoKrety Toolbox{/t}</a></li>
            <li><a id="navbar-resolver" href="/go2geo/">{fa icon="map-pin"} {t}Waypoint resolver{/t}</a></li>
        </ul>
    </li>
    {if GK_DEVEL}
        <li>
            <div class="navbar-btn">
                <div class="btn-group" role="group">
                    <a id="navbar-localmail" href="{'devel_home'|alias}" class="btn btn-danger">
                        {fa icon="wrench"} Dev
                    </a>
                    <a id="navbar-localmail" href="{'devel_mail_list'|alias}" class="btn btn-danger">
                        {fa icon="envelope"} Mailbox <span class="badge">{if is_countable($f3->get('SESSION.LOCAL_MAIL'))}{$f3->get('SESSION.LOCAL_MAIL')|count}{else}0{/if}</span>
                    </a>

                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {fa icon="sign-in"}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuSignInFast">
                            <li><a href="{'devel_login_user'|login_link:sprintf('username=%s', username1)}">User 1</a></li>
                            <li><a href="{'devel_login_user'|login_link:sprintf('username=%s', username2)}">User 2</a></li>
                            <li><a href="{'devel_login_user'|login_link:sprintf('username=%s', username3)}">User 3</a></li>
                            <li role="separator" class="divider"></li>
                            <li><a href="{'devel_logout_user'|alias}">Sign Out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </li>
    {/if}
</ul>
