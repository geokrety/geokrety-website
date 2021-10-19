{extends file='base.tpl'}

{block name=title}{t}Admin actions on users{/t}{/block}
{include file='macros/pagination.tpl'}

{block name=content}
    <h1>{t}Admin actions on users{/t}</h1>

    {block name=search_box}{/block}
    <a class="anchor" id="users-list"></a>

    {if isset($users) and $users.subset}
    {call pagination pg=$pg anchor='users-list'}
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th class="text-right">Join date</th>
            <th class="text-right">Last login</th>
            <th class="text-right">Email valid</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$users.subset item=item}
            <tr class="">
                <td>{$item->id}</td>
                <td>{$item|userlink:null:'_blank' nofilter}</td>
                <td>{$item->email}</td>
                <td class="text-right">{if !is_null($item->joined_on_datetime)}{$item->joined_on_datetime|print_date nofilter}{/if}</td>
                <td class="text-right">{if !is_null($item->last_login_datetime)}{$item->last_login_datetime|print_date nofilter}{/if}</td>
                <td class="text-right">{if $item->email_invalid}{t}false{/t}{else}{t}true{/t}{/if}</td>
                <td class="text-right">
                    {if !is_null($item->email) and !$item->email_invalid}
                    <button type="button" class="btn btn-warning btn-xs" title="{t}Invalidate user's email{/t}" data-toggle="modal" data-target="#modal" data-type="admin-users-email-invalidate" data-id="{$item->id}">
                        {fa icon="envelope"}
                    </button>
                    {/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {call pagination pg=$pg anchor='users-list'}
    {elseif isset($search) and !empty($search)}
    <em>{t escape=no}No users match the current request.{/t}</em>
    {/if}
{/block}

{block name=search_box}
    <div class="panel panel-default">
        <div class="panel-body">
            <p id="found-geokret-label">
                {t}Start by finding users:{/t}
            </p>
            <form class="form" action="{'admin_users_list'|alias}" method="get">
                <div class="form-group">
                    <input class="form-control" type="text" name="search" id="search" minlength="1" required placeholder="{t}Username or email or ID{/t}" {if isset($search)}value="{$search}"{/if}>
                </div>
                <button id="search-button" type="submit" class="btn btn-success">{t}Search{/t}</button>
            </form>
        </div>
    </div>
{/block}

{block name=javascript}
// Bind modal
{include 'js/dialogs/dialog_admin_users_email_invalidate.tpl.js'}
{/block}
