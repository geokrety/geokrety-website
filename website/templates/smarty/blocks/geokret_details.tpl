<div class="panel panel-default">
  <div class="panel-heading">
{if $geokret_details->ownerId}
    {call gk_icon gk_type=$geokret_details->type}
    {t escape=no
    gk_name={gklink gk=$geokret_details txt='name'}
    gk_type={gkType2Text type=$geokret_details->type}
    username={userlink user=$geokret_details->owner()}
}%1 (%2) by %3{/t}
{else}
    {call gk_icon gk_type=$geokret_details->type}
    {t escape=no
    gk_name={gklink gk=$geokret_details txt='name'}
    gk_type={gkType2Text type=$geokret_details->type}
    username={userlink user=$geokret_details->owner()}
}%1 (%2){/t} - {t}Ready for adoption{/t}
{/if}
    {if $geokret_details->isOwner()}
    <div class="btn-group pull-right" role="group">
      {if $geokret_details->owner()->email}
      <a class="btn btn-primary btn-xs" href="{$geokret_details->mailToUrl()}" title="{t}Send a message to the user{/t}">
        {fa icon="envelope"}
      </a>
      {/if}
      <a class="btn btn-warning btn-xs" href="{$geokret_details->editUrl()}" title="{t}Edit GeoKret details{/t}">
        {fa icon="pencil"}
      </a>
    </div>
    <div class="clearfix"></div>
    {/if}
  </div>
  <div class="panel-body">
    <div class="row">
      <div class="col-xs-12 col-md-9">
        <dl class="dl-horizontal pull-left">
          <dt>{t}Reference number{/t}</dt>
          <dd>{id2gk id=$geokret_details->id}</dd>
          {if $geokret_already_seen or $currentUser == $geokret_details->ownerId}
          <dt>{t}Tracking code{/t}</dt>
          <dd><strong>{$geokret_details->trackingCode}</strong></dd>
          {/if}
          <dt>{t}Total distance{/t}</dt>
          <dd>{$geokret_details->distance} km</dd>
          <dt>{t}Places visited{/t}</dt>
          <dd>{$geokret_details->cachesCount}</dd>
          <dt>{t}Social share{/t}</dt>
          <dd>
            {fa icon="facebook" title="{t}Share on Facebook{/t}"}
            {fa icon="twitter" title="{t}Share on Twitter{/t}"}
            {fa icon="instagram" title="{t}Share on Instagram{/t}"}
            {fa icon="link" title="{t}Copy link to page{/t}"}
            {fa icon="forumbee" title="{t}Copy to page as bbcode{/t}"}
          </dd>
          <dt>{t}Country track{/t}</dt>
          <dd>{country_track items=$country_track}</dd>
        </dl>
      </div>
      <div class="col-xs-12 col-md-3 gallery">
        {pictureOrDefault item=$geokret_details->avatar() skipLinkToEntity=true isOwner=$geokret_details->isOwner() pictureType=0 id=$geokret_details->id}
        {altitudeProfile gk_id=$geokret_details->id}
      </div>
    </div>

  </div>
</div>
