{extends file='base.tpl'}

{block name=title}🧺 {t}Owned GeoKrety{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_DATATABLE_CSS) && ''}
{\Assets::instance()->addJs(GK_CDN_DATATABLE_JS) && ''}

{include file='macros/pagination.tpl'}
{block name=content}
<a class="anchor" id="owned"></a>

<h2>🧺 {t}Owned GeoKrety{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        {if $geokrety_count}
        <div class="table-responsive">
            <table id="userOwnedGeoKretyTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>{t}ID{/t}</th>
                        <th>{t}Name{/t}</th>
                        <th class="text-center">{t}Spotted in{/t}</th>
                        <th class="text-center">{t}Last move{/t}</th>
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
        <em>{t escape=no url_map={'geokrety_map'|alias} url_create={'geokret_create'|alias}}You did not created any GeoKrety yet. Don't hesitate to <a href="%2">create your own GeoKrety</a> for free!{/t}</em>
        {else}
        <em>{t escape=no username=$user|userlink}%1 doesn't own any GeoKrety yet.{/t}</em>
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
$('#userOwnedGeoKretyTable').dataTable({
    {call common alias='user_owned'}
    "order": [[ 3, 'desc' ], [ 1, 'asc' ]],
    "columns": [
        { "name": "id" },
        { "name": "name" },
        { "searchable": false, "orderable": false },
        { "searchable": false, "name": "updated_on_datetime" },
        { "searchable": false, "name": "distance" },
        { "searchable": false, "name": "caches_count" },
        { "searchable": false, "orderable": false }
    ],
});
{/block}
