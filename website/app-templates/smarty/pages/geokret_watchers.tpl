{extends file='base.tpl'}

{block name=title}👓 {t}GeoKret watchers{/t}{/block}

{block name=content}
    {include file='macros/pagination.tpl'}
    <a class="anchor" id="geokret-watchers"></a>

    <h2>👓️ {t escape=no gk_name={$geokret|gklink nofilter}}Those users are watching GeoKret %1 moves{/t}</h2>
    <div class="row">
        <div class="col-xs-12">

            {if $users.subset}
                {call pagination pg=$pg anchor='geokret-watchers'}
                <div class="table-responsive">
                    <table id="geokretWatchersTable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>{t}Username{/t}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$users.subset item=item}
                            <tr>
                                <td>{$item|userlink nofilter}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                {call pagination pg=$pg anchor='geokret-watchers'}
            {else}

                <em>{t escape=no gk_name={$geokret|gklink nofilter}}No users are watching GeoKret %1{/t}</em>
            {/if}

        </div>
    </div>

{/block}
