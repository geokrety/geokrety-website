{extends file='base.tpl'}

{block name=title}🎉 {t}Welcome to GeoKrety.org!{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_ANIMATE_CSS) && ''}

{block name=content}
<h2>🎉 {t}Welcome to GeoKrety.org!{/t}</h2>

<div class="row" id="welcome_intro">
  <div class="col-md-9">
    <div class="row">
      {include file='banners/site_intro.tpl'}
    </div>
    <div class="row">
      {include file='banners/intro_stats.tpl'}
    </div>

    {if $news}
    <div class="row">
      <h2>📰 {t}News{/t}</h2>
      {foreach from=$news item=item}
      {include file='elements/news.tpl' news=$item}
      {/foreach}
    </div>
    {/if}

    {if $moves}
    <div class="row">
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
            {include file='elements/move_as_list.tpl' move=$moves}
          </tbody>
        </table>
      </div>
    </div>
    {/if}
  </div>
  <div class="col-md-3">
    {include file='banners/donate.tpl'}
    {include file='blocks/found_geokret.tpl'}
  </div>
</div>

{if $pictures}
<div class="row">
  <div class="col-md-12">
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
  </div>
</div>
{/if}

{if $geokrety}
<div class="row">
  <div class="col-md-12">
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
  </div>
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
