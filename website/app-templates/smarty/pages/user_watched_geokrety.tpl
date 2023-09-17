{extends file='base.tpl'}

{block name=title}🧺 {t}Watched GeoKrety{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_DATATABLE_CSS) && ''}
{\Assets::instance()->addJs(GK_CDN_DATATABLE_JS) && ''}

{block name=content}
<a class="anchor" id="watched"></a>

<h2>🧺 {t}Watched GeoKrety{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        {if $geokrety_count}
        <div class="table-responsive">
            <table id="userWatchedTable" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>{t}ID{/t}</th>
                        <th>{t}Name{/t}</th>
                        <th class="text-center">{t}Owner{/t}</th>
                        <th class="text-center">{t}Spotted in{/t}</th>
                        <th class="text-center">{t}Last log{/t}</th>
                        <th class="text-right">📏 {t}Distance{/t}</th>
                        <th class="text-right"><img src="{GK_CDN_IMAGES_URL}/log-icons/2caches.png" title="{t}Caches visited count{/t}" /></th>
                        <th class="text-center" title="{t}Actions{/t}">🔧</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        {else}

        {if $user->isCurrentUser()}
        <em>{t escape=no url_map={'geokrety_map'|alias} url_create={'geokret_create'|alias}}You did not watch any GeoKrety yet.{/t}</em>
        {else}
        <em>{t escape=no username=$user|userlink}%1 doesn't watch any GeoKrety yet.{/t}</em>
        {/if}

        {/if}

    </div>
    <div class="col-xs-12 col-md-3">
        {include file='blocks/user/actions.tpl'}
    </div>
</div>

{/block}

{include file='macros/datatable.tpl'}
{block name=javascript}
$('#userWatchedTable').dataTable({
    {call common alias='user_watched'}
    "order": [[ 4, 'desc' ], [ 1, 'asc' ]],
    "columns": [
        { "name": "id" },
        { "name": "name" },
        { "searchable": false, "name": "owner", "orderable": false },
        { "searchable": false, "name": "last waypoint", "orderable": false },
        { "searchable": false, "name": "gk_moves__last_position.moved_on_datetime" },
        { "searchable": false, "name": "distance" },
        { "searchable": false, "name": "caches_count" },
        { "searchable": false, "orderable": false },
    ],
});
{/block}
