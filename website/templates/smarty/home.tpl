{include file='macros/converters.tpl'}
{include file='macros/picture.tpl'}
{include file='macros/icons.tpl'}
<ol class="breadcrumb">
  <li class="active">{t}Home{/t}</li>
</ol>

<h2>{t}{$site_welcome}{/t}</h2>

<div class="row" id="welcome_intro">
  <div class="col-md-9">
    {include file='blocks/site_intro.tpl'}
    {include file='blocks/intro_stats.tpl'}
  </div>
  <div class="col-md-3">
    {include file='blocks/found_geokret.tpl'}
  </div>
</div>

<h2>{t}News{/t}</h2>
{foreach from=$news item=item}
{include file='blocks/news.tpl'}
{/foreach}

<h2>{t}Recent moves{/t}</h2>
{include file='blocks/geokrety_table_trip.tpl'}
<a href="/mapki/google_static_logs.png" data-preview-image="/mapki/google_static_logs.png">
  {t}Recent logs on the map{/t}
</a>

<h2>{t}Recently uploaded images{/t}</h2>
{include file='blocks/pictures_list.tpl'}
<a href="galeria.php">{t}Photo gallery{/t}</a>

<h2>{t}Recently registered GeoKrety{/t}</h2>
{include file='blocks/geokrety_table_recently_created.tpl'}

{if $online_users}
<h2>{t}Online users{/t}</h2>
{foreach from=$online_users item=item}
{userlink user=$item}
{/foreach}
{/if}

<div class="pull-right">
  {t total=$counter.total}%1 visits{/t} ({t visits=$counter.average}%1 visits/day{/t})
</div>
