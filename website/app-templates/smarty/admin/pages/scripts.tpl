{extends file='base.tpl'}

{block name=title}{t}Scripts management{/t}{/block}

{block name=content}
    <h1>
        {t}Scripts management{/t}
        <a href="{'admin_scripts'|alias}" class="btn btn-success" title="{t}Refresh{/t}">
            {fa icon="refresh"}
        </a>
    </h1>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th class="text-right">Last run</th>
            <th class="text-right">Last page</th>
            <th class="text-right">Locked</th>
            <th class="text-right">Acked</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
            {if $scripts}
            {foreach from=$scripts item=item}
                <tr class="{if $item->is_locked()}danger{/if}">
                    <td>{$item->id}</td>
                    <td>{$item->name}</td>
                    <td class="text-right">{if !is_null($item->last_run_datetime)}{$item->last_run_datetime|print_date nofilter}{/if}</td>
                    <td class="text-right">{if is_null($item->last_page)}{$item->last_page}{/if}</td>
                    <td class="text-right">{if $item->is_locked()}{$item->locked_on_datetime|print_date nofilter}{/if}</td>
                    <td class="text-right">{if $item->is_acked()}{$item->acked_on_datetime|print_date nofilter}{/if}</td>
                    <td class="text-right">
                        {block user_actions}{/block}
                    </td>
                </tr>
            {/foreach}
            {/if}
        </tbody>
    </table>
{/block}

{block user_actions}
<div class="btn-group" role="group" aria-label="...">
    {if $item->is_locked()}
        <button type="button" class="btn btn-warning btn-xs" title="{t}Unlock script{/t}" data-toggle="modal" data-target="#modal" data-type="admin-script-unlock" data-script-id="{$item->id}">
            {fa icon="lock"}
        </button>
        <button type="button" class="btn btn-success btn-xs" title="{t}Ack{/t}" data-toggle="modal" data-target="#modal" data-type="admin-script-ack" data-script-id="{$item->id}" {if $item->is_acked()}disabled{/if}>
            {fa icon="check"}
        </button>
    {/if}
</div>
{/block}

{block name=javascript}
// Bind modal
{include 'js/dialogs/dialog_admin_script_actions.tpl.js'}
{/block}
