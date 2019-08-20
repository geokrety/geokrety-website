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

<h2>{fa icon="newspaper-o"} {t}News{/t}</h2>
{foreach from=$news item=item}
{include file='elements/news.tpl' news=$item}
{/foreach}

<h2>{fa icon="newspaper-o"} {t}Latest moves{/t}</h2>

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

<h2>{t}🔮 Work In Progress{/t}</h2>
{include file='banners/wip.tpl'}
{/block}


{block name=javascript}
{if $f3->get('SESSION.IS_LOGGED_IN')}
    // Bind modal
    {include 'js/dialog_news_subscription.js.tpl'}
{/if}
{/block}
