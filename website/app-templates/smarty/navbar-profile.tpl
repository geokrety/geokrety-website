{if $f3->get('SESSION.IS_LOGGED_IN')}
<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
        <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
        {t}My account{/t} <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        <li><a href="{'user_details'|alias:sprintf('userid=%d', $f3->get('SESSION.CURRENT_USER'))}"><span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> {t}Profile{/t}</a></li>
        {if isset($isSuperUser) and $isSuperUser}
        <li><a href="_admin.php">{fa icon="support"} {t}Admin{/t}</a></li>
        {/if}
        <li role="separator" class="divider"></li>
        <li><a href="{'user_inventory'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="briefcase"} {t}My inventory{/t}</a></li>
        <li><a href="{'user_owned'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="bolt"} {t}My GeoKrety{/t}</a></li>
        <li><a href="{'user_watched'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="binoculars"} {t}Watched GeoKrety{/t}</a></li>
        <li role="separator" class="divider"></li>
        <li><a href="{'user_recent_moves'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="plane"} {t}My recent moves{/t}</a></li>
        <li><a href="{'user_owned_recent_moves'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="plane"} {t}Recent moves of my GeoKrety{/t}</a></li>
        <li role="separator" class="divider"></li>
        <li><a href="{'user_pictures'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="picture-o"} {t}My photos{/t}</a></li>
        <li><a href="{'user_owned_pictures'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}">{fa icon="picture-o"} {t}Photos of my GeoKrety{/t}</a>

        </li>
        <li role="separator" class="divider"></li>
        <li><a href="{'geokrety_map'|alias}#{\GeoKrety\Controller\Map::buildFragmentUserIdGeokrety($f3->get('SESSION.CURRENT_USER'))}">{fa icon="map"} {t}Where are my GeoKrety?{/t}</a></li>
        <li role="separator" class="divider"></li>
        <li><a href="{'logout'|alias}">{fa icon="sign-out"} {t}Sign out{/t}</a></li>
    </ul>
</li>
{else}
<li><a href="{'user_register'|alias}">{fa icon="user-plus"} {t}Create account{/t}</a></li>
<li><a href="{'login'|alias}?goto={urlencode($f3->get('PATH'))}">{fa icon="sign-in"} {t}Sign in{$f3->get('FRAGMENT')}{/t}</a>
    {/if}
