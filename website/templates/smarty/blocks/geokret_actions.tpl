{function watchers}
{capture "watchers"}{$geokret_watchers.html nofilter}{/capture}
<div class="col-md-4">{if $smarty.capture.watchers != '-'}{fa icon="archive"} {$smarty.capture.watchers nofilter}{/if}</div>
{/function}

{function log}
<div class="col-md-4">{fa icon="pencil"} <a href="/ruchy.php?nr={$geokret_details->trackingCode}">{t}Log this GeoKret{/t}</a></div>
{/function}

{function label}
<div class="col-md-4">{fa icon="tag"} <a href="/labels.php?id={$geokret_details->id}&nr={$geokret_details->trackingCode}">{t}Print a label for this GeoKret{/t}</a></div>
{/function}

{function statistics}
<div class="col-md-4">{fa icon="line-chart"} <a href="/gk_stat.php?id={$geokret_details->id}">{t}Statistics{/t}</a></div>
{/function}

{function adopt}
{if !$geokret_details->ownerId}
{if $isLoggedIn}
<div class="col-md-4">{fa icon="heart"} <a href="/claim.php">{t}Adopt this GeoKret{/t}</a></div>
{else}
<div class="col-md-4">{fa icon="heart"} <a href="/longin.php">{t}Login to claim this GeoKret{/t}</a></div>
{/if}
{/if}
{/function}

{function archive}
<div class="col-md-4">{fa icon="archive"} <a href="/ruchy.php?nr={$geokret_details->trackingCode}&type=archive">{t}Archive this GeoKret{/t}</a></div>
{/function}

{function email}
{if !$geokret_details->ownerId}
<div class="col-md-4">{fa icon="envelope"} <a href="/majluj.php?to={$geokret_details->ownerId}&re={$geokret_details->id}">{t}Email owner{/t}</a></div>
{/if}
{/function}

{function empty}
<div class="col-md-4">&nbsp;</div>
{/function}

<div class="panel panel-default">
  <div class="panel-heading">
    {t}Actions{/t}
  </div>
  <div class="panel-body">
    {if $isLoggedIn}
      {if $isGeokretOwner}
        <div class="row">
          {watchers}
          {log}
          {label}
        </div>
        <div class="row">
          {statistics}
          {empty}
        </div>
      {else}
        <div class="row">
          {watchers}
          {if $geokret_already_seen}{log}{else}{empty}{/if}
          {email}
        </div>
        <div class="row">
          {statistics}
          {adopt}
        </div>
      {/if}
    {else}
      <div class="row">
        {statistics}
        {adopt}
      </div>
    {/if}
  </div>
</div>
