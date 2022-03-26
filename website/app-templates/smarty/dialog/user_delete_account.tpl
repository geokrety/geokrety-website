{block name=modal_content}
<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to delete your account?{/t}</h4>
</div>

<form id="user-delete-account-form" name="userDeleteAccountForm" action="{'user_delete_account'|alias}" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
    <div class="modal-body">
        <ul>
            <li>{t user=GK_USER_DELETED_USERNAME}All your logs and comments will appear posted from "%1"{/t}</li>
            <li>{t}All your GeoKrety will be automatically archived{/t}</li>
            <li>{t}Your avatar pictures will be deleted{/t}</li>
        </ul>
        <div class="alert alert-danger" role="alert">{t}Warning!!! This will permanently delete your account.{/t}</div>
        <div class="form-group">
            <label for="operationInputResult">{t number1=$number1 number2=$number2}Please solve this operation: %1 + %2 = ?{/t}</label>
            <input type="number" class="form-control" id="operationInputResult" name="operation_result" min="0" placeholder="Result" required>
        </div>
        <div class="checkbox">
            <label>
                <input id="removeCommentContentCheckbox" type="checkbox" name="removeCommentContentCheckbox"> {t}Also remove all my comments content{/t}
            </label>
        </div>
    </div>
    <div class="modal-footer">
        {call csrf}
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
        <button type="submit" class="btn btn-danger">{t}Delete my account{/t}</button>
    </div>
</form>
{/block}
