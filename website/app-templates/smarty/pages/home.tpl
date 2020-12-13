{extends file='base.tpl'}

{block name=title}{t}🎉 Welcome to GeoKrety.org!{/t}{/block}

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

<div class="row">
  <div class="col-md-3 col-md-push-9">
    {include file='banners/donate.tpl'}
  </div>
  <div class="col-md-9 col-md-pull-3">
    {if $news}
    <h2>📰 {t}News{/t}</h2>
    {foreach from=$news item=item}
    {include file='elements/news.tpl' news=$item}
    {/foreach}
    {/if}

    {if $moves}
    <h2>🥾 {t}Latest moves{/t}</h2>
    <div class="table-responsive" id="recentMovesPanel">
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
  </div>
</div>


{if $pictures}
<h2>📷 {t}Recent pictures{/t}</h2>
<div class="panel panel-default picturesList" id="recentPicturesPanel">
    <div class="panel-body">
        <div class="gallery">
            {foreach from=$pictures item=picture}
                    {$picture|picture:true:true:false:true:true nofilter}
            {/foreach}
        </div>
    </div>
</div>
{/if}

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
  {include 'js/dialogs/dialog_picture_actions.tpl.js'}
{/if}
{/block}
