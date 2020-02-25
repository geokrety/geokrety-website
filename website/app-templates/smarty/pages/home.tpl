{extends file='base.tpl'}

{block name=content}
<h2>{t}🎉 Welcome to GeoKrety.org!{/t}</h2>

<div class="row" id="welcome_intro">
  <div class="col-md-9">
    {include file='banners/site_intro.tpl'}
    {include file='banners/intro_stats.tpl'}
  </div>
  <div class="col-md-3">
    {include file='blocks/found_geokret.tpl'}
  </div>
</div>

{if $news}
<h2>📰 {t}News{/t}</h2>
{foreach from=$news item=item}
{include file='elements/news.tpl' news=$item}
{/foreach}
{/if}

{if $moves}
<h2>🥾 {t}Latest moves{/t}</h2>
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th></th>
        <th>{t}ID{/t}</th>
        <th>{t}Spotted in{/t}</th>
        <th>{t}Comment{/t}</th>
        <th class="text-center">{t}Last move{/t}</th>
        <th class="text-right"><img src="{GK_CDN_IMAGES_URL}/log-icons/dist.gif" title="{t}Distance{/t}" /></th>
      </tr>
    </thead>
    <tbody>
{foreach from=$moves item=item}
{include file='elements/move_as_list.tpl' move=$item}
{/foreach}
    </tbody>
  </table>
</div>
{/if}


<h2>📷 {t}Recent pictures{/t}</h2>
{include file='banners/wip.tpl'}

{if $geokrety}
<h2>🐥 {t}Recently created GeoKrety{/t}</h2>
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th></th>
        <th>{t}ID{/t}</th>
        <th class="text-center">{t}Owner{/t}</th>
        <th class="text-center">{t}Born{/t}</th>
      </tr>
    </thead>
    <tbody>
{foreach from=$geokrety item=item}
{include file='elements/geokrety_as_list_recently_born.tpl' geokret=$item}
{/foreach}
    </tbody>
  </table>
</div>
{/if}

{/block}


{block name=javascript}
{if $f3->get('SESSION.IS_LOGGED_IN')}
    // Bind modal
    {include 'js/dialogs/dialog_news_subscription.tpl.js'}
{/if}
{/block}
