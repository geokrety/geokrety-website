{extends file='base.tpl'}

{block name=title}{t}Api Rate Limits{/t}{/block}

{block name=content}
    <h1>
        {t}Api Rate Limits{/t}
        <a href="{'admin_api_rate_limits'|alias}" class="btn btn-success" title="{t}Refresh{/t}">
            {fa icon="refresh"}
        </a>
    </h1>

    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Group</th>
            <th>Key</th>
            <th class="text-center">Tier</th>
            <th class="text-right">Used</th>
            <th>Limit</th>
            <th class="text-right">Left</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
            {foreach from=$current key=group item=rows}
            {foreach from=$rows key=wire_key item=row}
                <tr>
                    <td>{$group} ({$row.period})</td>
                    <td>{$wire_key}</td>
                    <td class="text-center">{if $row.tier == "ANONYMOUS"}-{else}{$row.tier}{/if}</td>
                    <td class="text-right">{$row.used}</td>
                    <td>/{$row.limit}</td>
                    <td class="text-right">{$row.left}</td>
                    <td class="text-right">
                        {block user_actions}{/block}
                    </td>
                </tr>
            {/foreach}
            {/foreach}
        </tbody>
    </table>
{/block}

{block user_actions}
<div class="btn-group" role="group" aria-label="...">
    <button type="button"
            class="btn btn-warning btn-xs"
            title="{t}Reset{/t}"
            data-toggle="modal"
            data-target="#modal"
            data-type="admin-rate-limit-reset"
            data-key="{$wire_key}"
            data-name="{$group}">
        {fa icon="refresh"}
    </button>
</div>
{/block}

{block name=javascript}
// Bind modal
{include 'js/dialogs/dialog_admin_rate_limits_actions.tpl.js'}
{/block}
