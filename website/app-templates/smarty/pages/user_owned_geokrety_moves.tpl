{extends file='base.tpl'}

{block name=title}🛩️ {t username=$user->username}%1's GeoKrety recent moves{/t}{/block}

{\GeoKrety\Assets::instance()->addCss(GK_CDN_DATATABLE_CSS) && ''}
{\GeoKrety\Assets::instance()->addJs(GK_CDN_DATATABLE_JS) && ''}

{block name=content}
<a class="anchor" id="recent-moves"></a>

<h2>🛩️ {t username=$user->username}%1's GeoKrety recent moves{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        {if $moves_count}
        <div class="table-responsive">
            <table id="userOwnedGeoKretyRecentMovesTable" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>{t}ID{/t}</th>
                        <th class="text-center">{t}Spotted in{/t}</th>
                        <th>{t}Comment{/t}</th>
                        <th>{t}Date{/t}</th>
                        <th class="text-right">📏 {t}Distance{/t}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        {else}

        <em>{t escape=no username=$user|userlink}%1's GeoKrety didn't moved yet.{/t}</em>

        {/if}

    </div>
    <div class="col-xs-12 col-md-3">
        {include file='blocks/user/actions.tpl'}
    </div>
</div>

{/block}

{include file='macros/datatable.tpl'}
{block name=javascript}
$('#userOwnedGeoKretyRecentMovesTable').dataTable({
    {call common alias='user_owned_recent_moves'}
    "order": [[ 0, 'desc' ]],
    "columns": [
        { "name": "id" },
        { "name": "geokret" },
        { "name": "waypoint" },
        { "searchable": false, "orderable": false },
        { "name": "moved_on_datetime" },
        { "name": "distance" },
    ],
});
{/block}
