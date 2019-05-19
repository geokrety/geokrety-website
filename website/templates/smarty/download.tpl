{include file='macros/paginate.tpl'}

<ol class="breadcrumb">
    <li><a href="/">{t}Home{/t}</a></li>
    <li class="active">{t}Download{/t}</li>
</ol>


<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><img src="{$iconsUrl}/language.svg" width="22" height="22" alt="language" /> {t}Translation{/t}</h3>
    </div>
    <div class="panel-body">
        {t escape=no url="https://crwd.in/geokrety"}Our translation files are now hosted on <a href="%1">crowdin</a>. Feel free to join the community if you wish to contribute in translating Geokrety.org in your language or fix some translation
        issues.{/t}
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><img src="{$iconsUrl}/mapa.png" width="24" height="20" alt="mapka" /> {t}Map of caches for Garmin units{/t}</h3>
    </div>
    <div class="panel-body">
        {t escape=no count="2019/05/19: 576697"}As we have been collecting waypoints of significant number of caches (%1) caches around the world (<a href="help.php#fullysupportedwaypoints">click here</a> for a list of supported services) we decided
        to generate a transparent map of those caches for garmin units (img format). Now you can have all those caches on one map (actually: mapset).{/t}
        {t escape=no url="geomapa.php"}Read more and <a href="%1">get the map</a>.{/t}
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">{fa icon="paint-brush"} {t}Design ressources{/t}</h3>
    </div>
    <div class="panel-body">
        <h4>{t}GeoKrety logo{/t}</h4>
        <ul>
            <li>{t}Basic GK logo: {/t}<a href="https://github.com/geokrety/GeoKrety-Graphic-Resources/blob/master/doodle/geokrety.svg">SVG</a> | <a href="https://cdn.geokrety.org/images/banners/geokrety.png">PNG</a></li>
            <li>{t}The mole itself: {/t}<a href="https://github.com/geokrety/GeoKrety-Graphic-Resources/blob/master/doodle/the-mole.svg">SVG</a> | <a href="https://github.com/geokrety/GeoKrety-Graphic-Resources/blob/master/doodle/the-mole.png">PNG</a></li>
            <li>{t}Other ressources: {/t}<a href="https://github.com/geokrety/GeoKrety-Graphic-Resources/doodle">{t}here{/t}</a></li>
        </ul>

        <h4>{t}Sample GeoKrety labels{/t}</h4>
        <em>{t}Please note, that you can create a label for your GeoKrety automatically, by clicking on the appropriate link on the GeoKret's page.{/t}</em>
        <ul>
            <li>{t count="1"}Sample label #%1: {/t}<a href="{$imagesUrl}/labels/geokret_label_v1.png">PNG</a></li>
            <li>{t count="2"}Sample label #%1: {/t}<a href="{$imagesUrl}/labels/geokret_label_v2.png">PNG</a></li>
            <li>{t count="3"}Sample label #%1: {/t}<a href="{$imagesUrl}/labels/geokret_label_3.png">PNG</a> | <a href="{$imagesUrl}/labels/geokret_label_3.svg">SVG</a></li>
            <li>{t count="4"}Sample label #%1: {/t}<a href="{$imagesUrl}/labels/geokret_label_4.png">PNG</a> | <a href="{$imagesUrl}/labels/geokret_label_4.svg">SVG</a></li>

            <li>{t}Old label design: {/t}<a href="{$imagesUrl}/labels/label_pl_en_de.cdr">CDR</a> | <a href="{$imagesUrl}/labels/label_pl_en_de.pdf">PDF</a> | <a href="'.CONFIG_CDN_IMAGES.'/labels/label_pl_en_de.emf">EMF</a></li>
        </ul>
    </div>
</div>
