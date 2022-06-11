{extends file='base.tpl'}

{block name=title}👓 {t}Search GeoKrety{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_DATATABLE_CSS) && ''}
{\Assets::instance()->addJs(GK_CDN_DATATABLE_JS) && ''}

{block name=content}
<a class="anchor" id="search-by-geokrety"></a>

<h2>👓️ {t search_geokrety=$search_geokrety}Found GeoKrety matching: %1{/t}</h2>
<div class="row">
    <div class="col-xs-12">

        {if $geokrety_count}
        <div class="table-responsive">
            <table id="searchByGeokretyTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>{t}ID{/t}</th>
                        <th>{t}Name{/t}</th>
                        <th class="text-center">{t}Owner{/t}</th>
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

        <em>{t search_geokrety=$search_geokrety}No GeoKrety matching: %1{/t}</em>
        {/if}

    </div>
</div>

{/block}

{include file='macros/datatable.tpl'}
{block name=javascript}
$('#searchByGeokretyTable').dataTable({
    {call common alias='search_by_geokret'}
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
