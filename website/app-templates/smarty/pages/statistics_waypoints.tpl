{extends file='base.tpl'}

{block name=title}{t}Waypoints statistics{/t}{/block}

{block name=content}
    <h1>{t}Waypoints statistics{/t}</h1>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Service code</th>
                <th>Service url</th>
                <th class="text-right">Waypoint count</th>
                <th class="text-right">Last synchronization</th>
                <th class="text-right">Revision</th>
                <th class="text-right">Last error</th>
                <th class="text-right">Error count</th>
                {if $f3->get('SESSION.IS_ADMIN')}
                <th class="text-right">Actions</th>
                {/if}
            </tr>
        </thead>
        <tbody>
        <tr>
            <td>GC</td>
            <td>{{GK_WAYPOINT_SERVICE_URL_GC}|link:null:_blank nofilter}</td>
            <td class="text-right">{$wpt_gc_count}</td>
            <td class="text-right"></td>
            <td class="text-right"></td>
            <td class="text-right"></td>
            <td class="text-right"></td>
        </tr>
        {if $wpt_oc !== false}
        {foreach from=$wpt_oc item=item}
        <tr class="{if !is_null($item->last_success_datetime) and $item->last_error_time_diff < constant('GK_WAYPOINT_SERVICE_REFRESH_INTERVAL_'|cat:$item->service_id)}success{else}danger{/if}">
            <td>{$item->service_id}</td>
            <td>{constant(sprintf('GK_WAYPOINT_SERVICE_URL_%s', $item->service_id))|link:null:_blank nofilter}</td>
            <td class="text-right">{$item->wpt_count}</td>
            <td class="text-right">{if !is_null($item->last_success_datetime)}{$item->last_success_datetime|print_date_expiration:2}{/if}</td>
            <td class="text-right">{if is_null($item->revision)}{t}N/A{/t}{else}{$item->revision}{/if}</td>
            {if !is_null($item->last_error_datetime)}
                <td class="text-right" data-toggle="tooltip" title="{$item->last_error}">{$item->last_error_datetime|print_date_expiration:2}</td>
            {else}
                <td></td>
            {/if}
            <td class="text-right">{$item->error_count}</td>
            {if $f3->get('SESSION.IS_ADMIN')}
            <td class="text-right">
                {if !is_null($item->revision)}
                <a href="{'statistics_waypoints_restart'|alias:sprintf('@service_id=%s', $item->service_id)}" class="btn btn-warning" title="{t}Force complete synchronization{/t}">
                    {fa icon="refresh"}
                </a>
                {/if}
            </td>
            {/if}
        </tr>
        {/foreach}
        {/if}
        </tbody>
    </table>
{/block}

{block name=javascript}
{/block}
