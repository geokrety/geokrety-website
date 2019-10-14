<nav class="navbar-inverse navbar-fixed-top sidebarNavigation" data-sidebarClass="navbar-inverse">
    <div class="container-fluid">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed left-navbar-toggle" data-toggle="collapse" data-target="#bs-navbar-collapse" aria-expanded="false">
                <span class="sr-only">{t}Toggle navigation{/t}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" id="navbar-brand" href="{'home'|alias}">GeoKrety.org</a>

            <div class="pull-right hidden-sm hidden-md hidden-lg">
                {if $f3->get('SESSION.IS_LOGGED_IN')}
                <a href="{'move_create'|alias}" class="btn btn-success navbar-btn">
                    {t}Log a GeoKret{/t}
                </a>
                {else}
                <a href="{login_link}" class="btn btn-primary navbar-btn">
                    {fa icon="sign-in"} {t}Sign in{/t}
                </a>
                {/if}
                &nbsp;
            </div>
        </div>

        <div class="collapse navbar-collapse" id="bs-navbar-collapse">
            <ul class="nav navbar-nav hidden-sm hidden-md hidden-lg">
                {include file="navbar-profile.tpl"}
            </ul>

            <ul class="nav navbar-nav">
                <li><a id="navbar-home" href="{'home'|alias}"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> {t}Home{/t}</a></li>
                <li><a id="navbar-new" href="{'news_list'|alias}">{fa icon="newspaper-o"} {t}News{/t}</a></li>
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
                    </ul>
                </li>
                <li><a id="navbar-map" href="{'geokrety_map'|alias:null:null:{\GeoKrety\Controller\Map::buildFragmentNearUserHome()}}">{fa icon="map"} {t}GeoKrety Map{/t}</a></li>
            </ul>

            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a id="navbar-lang" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                        <img src="{GK_CDN_ICONS_URL}/language.svg" width="14" />
                        <span title="{t}Currently selected language{/t}">{\Multilang::instance()->current|language:true}</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        {foreach $languages as $code => $lang}
                        <li><a id="navbar-lang-{$code}" href="{\Multilang::instance()->alias($f3->get('ALIAS'), $f3->get('PARAMS'), $code)}{if $f3->exists('GET')}?{http_build_query($f3->get('GET')) nofilter}{/if}">{$lang}</a></li>
                        {/foreach}
                    </ul>
                </li>

                <li class="dropdown">
                    <a id="navbar-help" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{fa icon="support"} {t}Help{/t} <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a id="navbar-help-help" href="{'help'|alias}">{fa icon="support"} {t}Help{/t}</a></li>
                        <li><a id="navbar-help-holes" href="{'mole_holes'|alias}">{fa icon="bed"} {t}Moleholes and GK hotels{/t}</a></li>
                        <li><a id="navbar-help-terms" href="{'terms_of_use'|alias}">{fa icon="legal"} {t}Terms of use{/t}</a></li>
                        <li><a id="navbar-help-press" href="{'press_corner'|alias}">{fa icon="newspaper-o"} {t}Press corner{/t}</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a id="navbar-help-api" href="{'help_api'|alias}">{fa icon="cog"} {t}GK interface / API{/t}</a></li>
                        <li role="separator" class="divider"></li>
                        {* TODO
                        <li><a id="navbar-stats" href="{'statistics'|alias}">{fa icon="bar-chart"} {t}Statistics{/t}</a></li>
                        <li role="separator" class="divider"></li>
                        *}
                        <li><a id="navbar-downloads" href="{'downloads'|alias}">{fa icon="download"} {t}Downloads{/t}</a></li>
                        <li><a id="navbar-toolbox" href="{'geokrety_toolbox'|alias}">{fa icon="cog"} {t}GeoKrety Toolbox{/t}</a></li>
                        <li><a id="navbar-resolver" href="go2geo/">{fa icon="map-pin"} {t}Waypoint resolver{/t}</a></li>
                    </ul>
                </li>
                {if is_null(GK_SMTP_HOST)}
                <li>
                    <p class="navbar-btn">
                        <a id="navbar-localmail" href="{'local_mail_list'|alias}" class="btn btn-danger btn-block">
                            {fa icon="envelope"} Dev Mailbox <span class="badge">{if is_countable($f3->get('SESSION.LOCAL_MAIL'))}{$f3->get('SESSION.LOCAL_MAIL')|count}{else}0{/if}</span>
                        </a>
                    </p>
                </li>
                {/if}
            </ul>
            <ul class="nav navbar-nav navbar-right hidden-xs">
                {include file="navbar-profile.tpl"}
            </ul>


        </div>




    </div>
</nav>
