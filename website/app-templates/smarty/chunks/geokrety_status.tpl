{function actionDate geokret=null}
{if !is_null($geokret->last_log)}
{$geokret->last_log->created_on_datetime|print_date nofilter}
{else}
{$geokret->created_on_datetime|print_date nofilter}
{/if}
{/function}
<p>
<a href="#" name="gkStatusIcon" data-trackingcode="{$geokret->trackingCode}" title="{t}Remove this GeoKret from the selection{/t}">{fa icon="check"}</a>
{t escape=no name="{$geokret|gklink nofilter}" avatar="{$geokret|gkavatar nofilter}" author="{$geokret->owner|userlink nofilter}"} %1 %2 by %3{/t}
<br>
{if !is_null($geokret->last_log->user)}
{t escape=no icon="{$geokret->last_log|logicon:true nofilter}" waypoint="{$geokret->last_position|cachelink nofilter}" date="{actionDate geokret=$geokret}" author="{$geokret->last_log->author|userlink nofilter}"}%1 %2 %3 by %4{/t}
{else}
{t escape=no icon="{$geokret->last_log|logicon:true nofilter}" date="{actionDate geokret=$geokret}"}%1 %2{/t}
{/if}
</p>
