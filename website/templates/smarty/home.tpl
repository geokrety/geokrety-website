{include file='macros/converters.tpl'}
{include file='macros/picture.tpl'}
{include file='macros/icons.tpl'}
{include file='macros/links_news.tpl'}
{include file='macros/links_user.tpl'}

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

<h2>{t}Recent logs{/t}</h2>
{include file='blocks/moves_table.tpl'}
<a href="/mapki/google_static_logs.png" data-preview-image="/mapki/google_static_logs.png">
  {t}Recent logs on the map{/t}
</a>

<h2>{t}Recently uploaded images{/t}</h2>
{include file='blocks/pictures_list.tpl'}
<a href="galeria.php">{t}Photo gallery{/t}</a>

<h2>{t}Recently registered GeoKrety{/t}</h2>
{include file='blocks/geokrety_table.tpl'}

<h2>{t}Online users{/t}</h2>
{foreach from=$online_users item=item}
{call userLink id=$item.user_id username=$item.username}
{/foreach}

<div class="pull-right">
  {t total=$counter.total}%1 visits{/t} ({t visits=$counter.average}%1 visits/day{/t})
</div>
