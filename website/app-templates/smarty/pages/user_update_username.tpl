{extends file='base.tpl'}

{block name=title}{t}Change username{/t}{/block}
{include file='macros/recaptcha.tpl'}

{block name=content}
<div class="panel panel-default">
    <div class="modal-header alert-info">
        <h4 class="modal-title" id="modalLabel">{t}Change your username{/t}</h4>
    </div>

    <form id="update-email" name="update-email" action="{'user_update_username'|alias}" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
        <div class="modal-body">

            <div class="form-group">
                {t}Once your username is changed, you will need to reconnect.{/t}
            </div>

            <div class="form-group">
                <label for="inputNewUsername">{t}Desired username{/t}</label>
                <input type="text" class="form-control" id="inputNewUsername" name="username" placeholder="{t}New username{/t}" value="{if isset($newUsername)}{$newUsername}{/if}" required minlength="{GK_SITE_USERNAME_MIN_LENGTH}" maxlength="{GK_SITE_USERNAME_MAX_LENGTH}" data-parsley-trigger="focusout" data-parsley-remote data-parsley-remote-validator="usernameFreeValidator" data-parsley-remote-options='{ "type": "POST" }' data-parsley-errors-messages-disabled data-parsley-debounce="500">
            </div>

            {call recaptcha}

        </div>
        <div class="modal-footer">
            {call csrf}
            <a class="btn btn-default" href="{'user_details'|alias:sprintf('userid=%d', $currentUser->id)}" title="{t}Back to user page{/t}" data-dismiss="modal">
                {t}Dismiss{/t}
            </a>
            <button type="submit" class="btn btn-info">{t}Change{/t}</button>
        </div>
    </form>
</div>
{/block}

{block name=javascript}
    {include 'js/parsley/usernameFree.js'}
{/block}
