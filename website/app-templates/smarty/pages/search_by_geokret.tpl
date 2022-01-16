{extends file='base.tpl'}

{block name=title}👓 {t}Search GeoKrety{/t}{/block}

{block name=content}
{include file='macros/pagination.tpl'}
<a class="anchor" id="search-by-geokrety"></a>

<h2>👓️ {t search_geokrety=$search_geokrety}Found GeoKrety matching: %1{/t}</h2>
<div class="row">
    <div class="col-xs-12">

        {if $geokrety.subset}
        {call pagination pg=$pg anchor='search-by-geokrety'}
        <div class="table-responsive">
            <table id="searchByGeokretyTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>{t}Name{/t}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$geokrety.subset item=item}
                    {include file='elements/geokrety_as_list_user_inventory.tpl' geokret=$item}
                    {/foreach}
                </tbody>
            </table>
        </div>
        {call pagination pg=$pg anchor='search-by-geokrety'}
        {else}

        <em>{t search_geokrety=$search_geokrety}No GeoKrety matching: %1{/t}</em>
        {/if}

    </div>
</div>

{/block}
