{extends file='base.tpl'}

{block name=title}{t}GeoKrety Toolbox - GKT{/t}{/block}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{fa icon="wrench"} {t}GeoKrety Toolbox - GKT{/t}</h3>
    </div>
    <div class="panel-body">

        <p><b>{t escape=no url1="https://geocaching.com"}Shows GeoKrety trackables on <a href="%1" target="_blank">geocaching.com</a> cache pages and facilitates dropping GeoKrety trackables into Geocaching caches.{/t}</b></p>

        <p><img src="{GK_CDN_IMAGES_URL}/help/gkt1.png" alt="screenshot" /></p>

        <p class="text-justify">{t escape=no url1="https://geocaching.com" url2="https://geokrety.org"}This script was written to build a bridge between the most popular <a href="%1">geocaching.com</a> site and <a href="%2">geokrety.org</a>. It has two functions. Once you enter a particular cache page on <a href="%1">geocaching.com</a> it will automatically check if there are any GeoKrety (items tracked on <a href="%2">geokrety.org</a>) inside that cache and show the result in the inventory section on the right hand side of the screen (below the existing list of Travelbugs and Geocoins). If you have an account on <a href="%2">geokrety.org</a> you can also easily drop GeoKrety into <a href="%1">Geocaching</a> caches because the waypoint code and coordinates are automatically copied over onto the logging form on <a href="%2">geokrety.org</a>.{/t}</p>

        <p>{t}Available for:{/t}</p>

        <ul>
            <li><a href="https://chrome.google.com/webstore/detail/geokrety-toolbox/ldbheajkebdflbjdckojokbfdndkahnl?hl=en-US">Google Chrome</a></li>
            <li>{t escape=no url1="https://raw.githubusercontent.com/geokrety/GeoKrety-Toolbox/master/GeoKrety.Toolbox.user.js" url2="https://addons.mozilla.org/en-US/firefox/addon/tampermonkey/" url3="https://addons.mozilla.org/en-US/firefox/addon/greasemonkey/"}<a href="%1">Firefox</a> (needs <a href="%2">Tampermonkey add-on</a> or <a href="%3">greasemonkey add-on</a>){/t}</li>
        </ul>

        <p>{t}Note: the "Drop a GK in this cache" feature will probably require you to disable "Cookie tracking protection" otherwise the addon will not detect your authentication on GeoKrety.org{/t}</p>

    </div>
</div>
{/block}
