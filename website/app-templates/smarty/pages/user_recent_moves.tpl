{extends file='base.tpl'}

{block name=title}🛩️ {t username=$user->username}%1's recent moves{/t}{/block}

{block name=content}
{include file='macros/pagination.tpl'}
<a class="anchor" id="recent-moves"></a>

<h2>🛩️ {t username=$user->username}%1's recent moves{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        {if $moves.subset}
        {call pagination pg=$pg anchor='recent-moves'}
        <div class="table-responsive">
            <table id="userRecentMovesTable" class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>{t}ID{/t}</th>
                        <th class="text-center">{t}Spotted in{/t}</th>
                        <th>{t}Comment{/t}</th>
                        <th class="text-right">{t}Last move{/t}</th>
                        <th class="text-right"><img src="{GK_CDN_IMAGES_URL}/log-icons/2caches.png" title="{t}Caches visited count{/t}" /></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$moves.subset item=item}
                    {include file='elements/move_as_list.tpl' move=$item}
                    {/foreach}
                </tbody>
            </table>
        </div>
        {call pagination pg=$pg anchor='recent-moves'}
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
