{block name=user_finder_list}
{include file='macros/pagination.tpl'}
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
            <th class="text-right">Status</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$users.subset item=user}
            <tr class="">
                <td>{$user->id}</td>
                <td>{$user|userlink:null:'_blank' nofilter}</td>
                <td>{$user->email}</td>
                <td class="text-right">{if !is_null($user->joined_on_datetime)}{$user->joined_on_datetime|print_date nofilter}{/if}</td>
                <td class="text-right">{if !is_null($user->last_login_datetime)}{$user->last_login_datetime|print_date nofilter}{/if}</td>
                <td class="text-right">{if $user->email_invalid}{t}false{/t}{else}{t}true{/t}{/if}</td>
                <td class="text-right">{$user->status_text()}</td>
                <td class="text-right">
                    {block user_actions}{/block}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {call pagination pg=$pg anchor='users-list'}
{elseif isset($search) and !empty($search)}
    <em>{t}No users match the current request.{/t}</em>
{/if}
{/block}
