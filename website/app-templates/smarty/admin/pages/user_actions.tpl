{extends file='base.tpl'}

{block name=title}{t}Admin actions on users{/t}{/block}

{block name=content}
    <h1>{t}Admin actions on users{/t}</h1>

    {block name=user_finder}{/block}
    {block name=user_finder_list}{/block}
{/block}
{include file='blocks/user_finder.tpl'}
{include file='blocks/user_finder_list.tpl'}

{block user_actions}
<div class="btn-group" role="group" aria-label="...">
    {if !is_null($user->email) and !$user->email_invalid}
    <button type="button" class="btn btn-warning btn-xs" title="{t}Invalidate user's email{/t}" data-toggle="modal" data-target="#modal" data-type="admin-users-email-invalidate" data-id="{$user->id}">
        {fa icon="envelope"}
    </button>
    {/if}
    <button type="button" class="btn btn-success btn-xs" title="{t}Award prize{/t}" data-toggle="modal" data-target="#modal" data-type="admin-users-award-prize" data-user-id="{$user->id}">
        {fa icon="gavel"}
    </button>
</div>
{/block}

{block name=javascript}
// Bind modal
{include 'js/dialogs/dialog_admin_users_email_invalidate.tpl.js'}
{include 'js/dialogs/dialog_admin_users_prize_awarder_manual.tpl.js'}
{/block}

{block name=javascript_modal append}
$('body').on('change', '#award-selector', function(event) {
    let award_url = $('#award-selector option:selected').data('url');
    $('#award-preview').attr('src', award_url);
    $('#award-button').prop("disabled", award_url === '');
});
{/block}
