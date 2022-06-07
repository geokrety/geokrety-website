{extends file='base.tpl'}

{block name=title}🧺 {t}Watched GeoKrety{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_DATATABLE_CSS) && ''}
{\Assets::instance()->addJs(GK_CDN_DATATABLE_JS) && ''}

{block name=content}
{include file='macros/pagination.tpl'}
<a class="anchor" id="watched"></a>

<h2>🧺 {t}Watched GeoKrety{/t}</h2>
<div class="row">
    <div class="col-xs-12 col-md-9">

        {if $geokrety.subset}
        {call pagination pg=$pg anchor='watched'}
        <div class="table-responsive">
            <table id="userWatchedTable" class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>{t}ID{/t}</th>
                        <th class="text-center">{t}Owner{/t}</th>
                        <th class="text-center">{t}Spotted in{/t}</th>
                        <th class="text-center">{t}Last move{/t}</th>
                        <th class="text-right">📏 {t}Distance{/t}</th>
                        <th class="text-right"><img src="{GK_CDN_IMAGES_URL}/log-icons/2caches.png" title="{t}Caches visited count{/t}" /></th>
                        <th class="text-center" title="{t}Actions{/t}">🔧</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$geokrety.subset item=item}
                    {include file='elements/geokrety_as_list_user_watched_geokrety.tpl' geokret=$item}
                    {/foreach}
                </tbody>
            </table>
        </div>
        {call pagination pg=$pg anchor='watched'}
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
