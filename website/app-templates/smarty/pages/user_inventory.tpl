{extends file='base.tpl'}

{block name=title}🎒 {t username=$user->username}%1's inventory{/t}{/block}

{block name=content}
{include file='macros/pagination.tpl'}
<a class="anchor" id="inventory"></a>

<h2>🎒 {t username=$user->username}%1's inventory{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        {if $geokrety.subset}
        {call pagination pg=$pg anchor='inventory'}
        <div class="table-responsive">
            <table id="userInventoryTable" class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>{t}ID{/t}</th>
                        <th class="text-center">{t}Owner{/t}</th>
                        <th class="text-center">{t}Last move{/t}</th>
                        <th class="text-right">📏 {t}Distance{/t}</th>
                        <th class="text-right"><img src="{GK_CDN_IMAGES_URL}/log-icons/2caches.png" title="{t}Caches visited count{/t}" /></th>
                        <th class="text-center" title="{t}Actions{/t}">🔧</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$geokrety.subset item=item}
                    {include file='elements/geokrety_as_list_user_inventory.tpl' geokret=$item}
                    {/foreach}
                </tbody>
            </table>
        </div>
        {call pagination pg=$pg anchor='inventory'}
        {else}

        {if $user->isCurrentUser()}
        <em>{t escape=no url_map={'geokrety_map'|alias} url_create={'geokret_create'|alias}}Your inventory is empty. Check <a href="%1">the map</a> and try to find GeoKrety near you! You can also <a href="%2">create your own GeoKrety</a> for free!{/t}</em>
        {else}
        <em>{t escape=no username=$user|userlink}%1's inventory is currently empty.{/t}</em>
        {/if}

        {/if}

    </div>
    <div class="col-xs-12 col-md-3">
        {include file='blocks/user/actions.tpl'}
    </div>
</div>

{/block}
