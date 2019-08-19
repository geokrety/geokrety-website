{function watchers}
<div class="col-md-4">{fa icon="archive"} <a href="#">{t}Watchers{/t}</a></div>
{/function}

{function log}
<div class="col-md-4">{fa icon="pencil"} <a href="#">{t}Log this GeoKret{/t}</a></div>
{/function}

{function label}
<div class="col-md-4">{fa icon="tag"} <a href="{'geokret_label_generator'|alias}">{t}Print a label for this GeoKret{/t}</a></div>
{/function}

{function statistics}
<div class="col-md-4">{fa icon="line-chart"} <a href="#">{t}Statistics{/t}</a></div>
{/function}

{function adopt}
{if !$geokret->owner}
{if $f3->get('SESSION.CURRENT_USER')}
<div class="col-md-4">{fa icon="heart"} <a href="{'geokret_claim'|alias}">{t}Adopt this GeoKret{/t}</a></div>
{else}
<div class="col-md-4">{fa icon="heart"} <a href="{'login'|alias}">{t}Login to claim this GeoKret{/t}</a></div>
{/if}
{/if}
{/function}

{function archive}
<div class="col-md-4">{fa icon="archive"} <a href="#">{t}Archive this GeoKret{/t}</a></div>
{/function}

{function email}
{if $geokret->owner->email}
<div class="col-md-4">{fa icon="envelope"} <a href="{'mail_by_geokret'|alias}">{t}Email owner{/t}</a></div>
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
    {if $f3->get('SESSION.CURRENT_USER')}
      {if $geokret->isOwner()}
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
      </div>
    {/if}
  </div>
</div>
