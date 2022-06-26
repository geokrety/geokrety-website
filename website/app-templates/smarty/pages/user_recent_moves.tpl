{extends file='base.tpl'}

{block name=title}🛩️ {t username=$user->username}%1's recent moves{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_DATATABLE_CSS) && ''}
{\Assets::instance()->addJs(GK_CDN_DATATABLE_JS) && ''}

{block name=content}
<a class="anchor" id="recent-moves"></a>

<h2>🛩️ {t username=$user->username}%1's recent moves{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        {if $moves_count}
        <div class="table-responsive">
            <table id="userRecentMovesTable" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>{t}ID{/t}</th>
                        <th>{t}Name{/t}</th>
                        <th class="text-center">{t}Spotted in{/t}</th>
                        <th>{t}Comment{/t}</th>
                        <th class="text-right">{t}Last move{/t}</th>
                        <th class="text-right"><img src="{GK_CDN_IMAGES_URL}/log-icons/2caches.png" title="{t}Caches visited count{/t}" /></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        {else}

        {if $user->isCurrentUser()}
        <em>{t escape=no url_map={'geokrety_map'|alias} url_create={'geokret_create'|alias}}You did not moved any GeoKrety yet. Check <a href="%1">the map</a> and try to find GeoKrety near you!{/t}</em>
        {else}
        <em>{t escape=no username=$user|userlink}%1 didn't moved any GeoKrety yet.{/t}</em>
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
$('#userRecentMovesTable').dataTable({
    {call common alias='user_recent_moves'}
    "order": [[ 0, 'desc' ]],
    "columns": [
        { "name": "id" },
        { "name": "geokret" },
        { "name": "waypoint" },
        { "searchable": false, "orderable": false },
        { "searchable": false, "name": "moved_on_datetime" },
        { "searchable": false, "name": "distance" },
    ],
});
{/block}
