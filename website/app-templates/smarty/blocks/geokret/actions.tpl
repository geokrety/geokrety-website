{function watchers}
<div class="col-md-4">{fa icon="archive"} <a id="geokretDetailsWatchersLink" href="#">{t}Watchers{/t}</a></div>
{/function}

{function log}
<div class="col-md-4">{fa icon="pencil"} <a id="geokretDetailsLogThisGeokretLink" href="{'move_create'|alias}?tracking_code={$geokret->tracking_code}">{t}Log this GeoKret{/t}</a></div>
{/function}

{function label}
{if $geokret->hasTouchedInThePast()}
<div class="col-md-4">{fa icon="tag"} <a id="geokretDetailsPrintLabelLink" href="{'geokret_label'|alias}">{t}Print a label for this GeoKret{/t}</a></div>
{/if}
{/function}

{function statistics}
<div class="col-md-4">&nbsp;</div>
{* TODO
<div class="col-md-4">{fa icon="line-chart"} <a href="#">{t}Statistics{/t}</a></div>
*}
{/function}

{function adopt}
{if !$geokret->owner}
{if $f3->get('SESSION.CURRENT_USER')}
<div class="col-md-4">{fa icon="heart"} <a id="geokretDetailsClaimLink" href="{'geokret_claim'|alias}">{t}Adopt this GeoKret{/t}</a></div>
{else}
<div class="col-md-4">{fa icon="heart"} <a id="geokretDetailsClaimLoginLink" href="{'login'|login_link}">{t}Login to claim this GeoKret{/t}</a></div>
{/if}
{/if}
{/function}

{function archive}
{if !$geokret->isArchived()}
<div class="col-md-4">{fa icon="archive"} <a id="geokretDetailsArchiveLink" href="#" title="{t}Mark this GeoKret as archived{/t}" data-toggle="modal" data-target="#modal" data-type="geokret-mark-archived" data-id="{$geokret->gkid}">{t}Archive this GeoKret{/t}</a></div>
{/if}
{/function}

{function email}
{if $geokret->owner && $geokret->owner->email}
<div class="col-md-4">{fa icon="envelope"} <a id="geokretDetailsEmailOwnersLink" href="{'mail_by_geokret'|alias:sprintf('@gkid=%s', $geokret->gkid)}">{t}Email owner{/t}</a></div>
{/if}
{/function}

{function transfer}
<div class="col-md-4">{fa icon="handshake-o"}
    <a id="geokretDetailsOfferAdoptionLink" href="#" title="{t}Make this GeoKret available for adoption by another user{/t}" data-toggle="modal" data-target="#modal" data-type="geokret-offer-for-adoption" data-id="{$geokret->gkid}">
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
        <div class="row">
            {empty}
            {empty}
            {archive}
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
