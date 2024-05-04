{block name=modal_content}
<div class="modal-header alert-warning">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Select new email status{/t}</h4>
</div>

<form name="UserEmailInvalidateForm" action="{'admin_users_email_invalidate'|alias:sprintf('userid=%d', $user->id)}" method="post">
    <div class="modal-body">
        <div class="form-group">
            <label for="inputEmailStatus" class="col-sm-3 control-label">{t}Email status{/t}</label>
            <div class="col-sm-9">
                <select class="form-control" id="inputEmailStatus" name="email_status">
                    {foreach GeoKrety\Model\User::USER_EMAIL_TEXT as $key => $value}
                        <option value="{$key}" {if $user->email_invalid === $key} selected{/if}>{$value}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-warning">{t}Update{/t}</button>
    </div>
</form>
{/block}
