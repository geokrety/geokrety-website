{include file='macros/paginate.tpl'}

<ol class="breadcrumb">
    <li><a href="/">{t}Home{/t}</a></li>
    <li><a href="/download.php">{t}Download{/t}</a></li>
    <li class="active">{t}Garmin maps{/t}</li>
</ol>

<ol>
    <li><a href="#map-geocaches">{t}Geocaches map{/t}</a></li>
    <li><a href="#map-confluence">{t}Confluences map{/t}</a></li>
</ol>

<a class="anchor" id="map-geocaches"></a>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{fa icon="map"} {t}Geocaches map{/t}</h3>
    </div>
    <div class="panel-body">
        <h3>Intro</h3>
        <p>
            {t count="2019/05/19: 576697" }As we have been collecting waypoints of significant number of caches (%1) caches around the world - click here for a list of supported services) we decided to generate a transparent map of those caches for garmin units (img format). Now you can have all those caches on one map (actually: mapset).{/t}
        </p>

        <h3>{t}Details{/t}</h3>
        <p>{t}The map is designed for newer units (Garmin Nuvi, HCx), but with older ones should also work fine.{/t}</p>
        <p>{t}The map was tested on Garmin Vista HCx, but on other recivers should also work fine (If you have a nice screenshot from your receiver, please send us. We would like to see the map in action!).{/t}</p>
        <p>
            {t}Iconology:{/t}
            <ul>
                <li>{t}Yellow - active cache / unknown status{/t}</li>
                <li>{t}Red - inactive cache{/t}</li>
            </ul>
        </p>

        <h3>{t}Download{/t}</h3>
        <p>{t}The map was last generated on Sat, 07 Oct 2017 and is still available as:{/t}</p>
        <ul>
            <li>
                {t escape=no url="{$cdnUrl}/rzeczy/mapa-f/out/geocaches.exe"}<a href="%1">MapSource installer</a>{/t}
                <pre>md5: 80ffd4465c345e7286509cfb39414527  geocaches.exe 4,8M</pre>
            </li>
            <li>
                {t escape=no url="{$cdnUrl}/rzeczy/mapa-f/out/geocaches.zip"}<a href="%1">Zip archive</a>{/t}
                <pre>md5: efb4628b1a2cf14e5b7be6b7ac3e7fce  geocaches.zip 4,8M</pre>
            </li>
        </ul>

        <h3>{t}Screenshots{/t}</h3>
        <p>
            <img src="{$imagesUrl}/geomap/garmin1.png" alt="icons" width="176" height="220" />
            <img src="{$imagesUrl}/geomap/garmin2.png" alt="russian" width="176" height="220" />
            <img src="{$imagesUrl}/geomap/garmin3.png" alt="australian" width="176" height="220" />
            <img src="{$imagesUrl}/geomap/garmin-szukaj.png" alt="How to find" width="176" height="220" />
            <img src="{$imagesUrl}/geomap/garmin-szukaj2.png" alt="a" width="176" height="220" />
            <img src="{$imagesUrl}/geomap/garmin-ustawienia.png" alt="settings" width="176" height="220" />
        </p>

        <p>{t author1="Angelo" author2="filips"}Authors: %1 & %2{/t}</p>
    </div>
</div>

<a class="anchor" id="map-confluence"></a>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{fa icon="crosshairs"} {t}Confluences map{/t}</h3>
    </div>
    <div class="panel-body">
        <h3>Intro</h3>
        <p>
            {t url="http://confluence.org/" }From confluence.org: <em>The goal of the project is to visit each of the latitude and longitude integer degree intersections in the world, and to take pictures at each location</em>. This is a transparent map with marked those points. For more details of the project, please visit the <a href="%1">Degree Confluence Project webpage</a>.{/t}
        </p>

        <h3>{t}Download{/t}</h3>
        <p>{t}The map was gnerated once, so there is no need to make updates :){/t}</p>
        <ul>
            <li>
                {t escape=no url="{$cdnUrl}/rzeczy/mapa-f/confluence/confluence.exe"}<a href="%1">MapSource installer</a>{/t}
                <pre>f0cb9237f38ad240dad2814f492859d0  confluence.exe 1,4M</pre>
            </li>
            <li>
                {t escape=no url="{$cdnUrl}/rzeczy/mapa-f/confluence/confluence.zip"}<a href="%1">Zip archive</a>{/t}
                <pre>db5955e12c4d7153ee5d8fa918a8bacf  confluence.zip 1,1M</pre>
            </li>
        </ul>

        <h3>{t}Screenshots{/t}</h3>
        <p>
            <img src="{$imagesUrl}/geomap/confluence.png" alt="icons" width="176" height="220" />
        </p>

        <p>{t author="filips"}Author: %1{/t}</p>
    </div>
</div>
