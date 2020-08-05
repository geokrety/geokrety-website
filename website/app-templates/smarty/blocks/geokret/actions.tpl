{function watchers}
<div class="col-md-4">{fa icon="archive"} <a href="#">{t}Watchers{/t}</a></div>
{/function}

{function log}
<div class="col-md-4">{fa icon="pencil"} <a href="{'move_create'|alias}?tracking_code={$geokret->tracking_code}">{t}Log this GeoKret{/t}</a></div>
{/function}

{function label}
{if $geokret->hasTouchedInThePast()}
<div class="col-md-4">{fa icon="tag"} <a href="{'geokret_label'|alias}">{t}Print a label for this GeoKret{/t}</a></div>
{/if}
{/function}

{function statistics}
{* TODO
<div class="col-md-4">{fa icon="line-chart"} <a href="#">{t}Statistics{/t}</a></div>
*}
{/function}

{function adopt}
{if !$geokret->owner}
{if $f3->get('SESSION.CURRENT_USER')}
<div class="col-md-4">{fa icon="heart"} <a href="{'geokret_claim'|alias}">{t}Adopt this GeoKret{/t}</a></div>
{else}
<div class="col-md-4">{fa icon="heart"} <a href="{login_link}">{t}Login to claim this GeoKret{/t}</a></div>
{/if}
{/if}
{/function}

{function archive}
<div class="col-md-4">{fa icon="archive"} <a href="#">{t}Archive this GeoKret{/t}</a></div>
{/function}

{function email}
{if $geokret->owner && $geokret->owner->email}
<div class="col-md-4">{fa icon="envelope"} <a href="{'mail_by_geokret'|alias:sprintf('@gkid=%s', $geokret->gkid)}">{t}Email owner{/t}</a></div>
{/if}
{/function}

{function transfer}
<div class="col-md-4">{fa icon="handshake-o"}
    <a href="#" title="{t}Make this GeoKret available for adoption by another user{/t}" data-toggle="modal" data-target="#modal" data-type="geokret-offer-for-adoption" data-id="{$geokret->gkid}">
        {t}Transfer ownership{/t}
    </a>
</div>
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
            {transfer}
        </div>
        {else}
        <div class="row">
            {watchers}
            {if $geokret->hasTouchedInThePast()}{log}{else}{empty}{/if}
            {email}
        </div>
        <div class="row">
            {statistics}
            {adopt}
            {label}
        </div>
        {/if}
        {else}
        <div class="row">
            {statistics}
        </div>
        {/if}
    </div>
</div>
