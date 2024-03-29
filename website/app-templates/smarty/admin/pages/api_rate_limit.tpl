{extends file='base.tpl'}

{block name=title}{t}Api Rate Limits{/t}{/block}

{assign var=RATES_LIMITS value=constant('GK_RATE_LIMITS')}
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
            <th>ID</th>
            <th class="text-right">Count</th>
            <th>Limit</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
            {foreach from=$current key=group item=item}
            {foreach from=$item key=key item=value}
                <tr>
                    <td>{$group} ({$RATES_LIMITS[$group][1]})</td>
                    <td>{$key}</td>
                    <td class="text-right">{$value}</td>
                    <td>/{$RATES_LIMITS[$group][0]}</td>
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
    <button type="button" class="btn btn-warning btn-xs" title="{t}Reset{/t}" data-toggle="modal" data-target="#modal" data-type="admin-rate-limit-reset" data-key="{$key}" data-NAME="{$group}">
        {fa icon="refresh"}
    </button>
</div>
{/block}

{block name=javascript}
// Bind modal
{include 'js/dialogs/dialog_admin_rate_limits_actions.tpl.js'}
{/block}
