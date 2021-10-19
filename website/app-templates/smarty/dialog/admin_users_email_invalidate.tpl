{block name=content}
<div class="modal-header alert-warning">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to invalidate this user's email?{/t}</h4>
</div>

<form name="UserEmailInvalidateForm" action="{'admin_users_email_invalidate'|alias:sprintf('userid=%d', $user->id)}" method="post">
    <div class="modal-body">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-warning">{t}Invalidate{/t}</button>
    </div>
</form>
{/block}
