{extends file='base.tpl'}

{block name=title}{t}Scripts management{/t}{/block}

{block name=content}
    <h1>{t}Scripts management{/t}</h1>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th class="text-right">Last run</th>
            <th class="text-right">Last page</th>
            <th class="text-right">Locked</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
            {foreach from=$scripts item=item}
                <tr class="{if !is_null($item->locked_datetime)}danger{/if}">
                    <td>{$item->id}</td>
                    <td>{$item->name}</td>
                    <td class="text-right">{if !is_null($item->last_run_datetime)}{$item->last_run_datetime|print_date nofilter}{/if}</td>
                    <td class="text-right">{if is_null($item->last_page)}{$item->last_page}{/if}</td>
                    <td class="text-right">{if !is_null($item->locked_datetime)}{$item->locked_datetime|print_date nofilter}{/if}</td>
                    <td class="text-right">
                        {if !is_null($item->locked_datetime)}
                            <a href="{'admin_scripts_unlock'|alias:sprintf('@script_id=%d', $item->id)}" class="btn btn-warning" title="{t}Unlock script{/t}">
                                {fa icon="lock"}
                            </a>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{/block}

{block name=javascript}
{/block}
