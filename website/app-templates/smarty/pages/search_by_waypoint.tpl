{extends file='base.tpl'}

{block name=title}👓 {t waypoint=$waypoint}GeoKrety visiting the cache %1{/t}{/block}

{block name=content}
{include file='macros/pagination.tpl'}
<a class="anchor" id="search-by-waypoint"></a>

<h2>👓️ {t waypoint=$waypoint}GeoKrety visiting the cache %1{/t}</h2>
<div class="row">
    <div class="col-xs-12">

        {if $moves.subset}
        {call pagination pg=$pg anchor='search-by-waypoint'}
        <div class="table-responsive">
            <table id="searchByWaypointTable" class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>{t}Name{/t}</th>
                        <th class="text-center">{t}Cache{/t}</th>
                        <th>{t}Comment{/t}</th>
                        <th class="text-center">{t}Date{/t}</th>
                        <th class="text-right">📏 {t}Distance{/t}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$moves.subset item=item}
                    {include file='elements/move_as_list_search_by_waypoint.tpl' move=$item}
                    {/foreach}
                </tbody>
            </table>
        </div>
        {call pagination pg=$pg anchor='search-by-waypoint'}
        {else}

        <em>{t escape=no waypoint=$waypoint}No GeoKrety has visited cache %1 yet.{/t}</em>
        {/if}

    </div>
</div>

{/block}
