{function actionDate geokret=null}
{if $geokret->lastLog->ruchData}
{print_date date=$geokret->lastLog->ruchData}
{else}
{print_date date=$geokret->datePublished}
{/if}
{/function}
<p>
<a href="#" name="gkStatusIcon" data-trackingcode="{$geokret->trackingCode}" title="{t}Remove this GeoKret from the selection{/t}">{fa icon="check"}</a>
{t escape=no name="{gklink gk=$geokret txt='gk-name' target='_blank'}" avatar="{gkavatar gk=$geokret}" author="{userlink user=$geokret->owner() target='_blank'}"} %1 %2 by %3{/t}
<br>
{if isset($geokret->lastLog->userId)}
{t escape=no icon="{logicon gk=$geokret}" waypoint="{cachelink tripStep=$geokret->lastPosition target='_blank' includeName=false}" date="{actionDate geokret=$geokret}" author="{userlink user=$geokret->lastLog->author() target='_blank'}"}%1 %2 %3 by %4{/t}
{else}
{t escape=no icon="{logicon gk=$geokret}" date="{actionDate geokret=$geokret}"}%1 %2{/t}
{/if}
</p>
