
{\Assets::instance()->addCss(GK_CDN_STRENGTHIFY_CSS)}
{\Assets::instance()->addJs(GK_CDN_STRENGTHIFY_JS)}

{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Change your password{/t}</h4>
</div>

<form id="update-password" name="update-password" action="{'user_update_password'|alias}" method="post" class="form-horizontal" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
    <div class="modal-body">

        <div class="form-group">
            <label for="inputPasswordOld" class="col-sm-2 control-label">{t}Current password{/t}</label>
            <div class="col-sm-8">
                <input type="password" class="form-control" id="inputPasswordOld" name="password_old" placeholder="{t}Old password{/t}" required>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label for="inputPasswordNew" class="col-sm-2 control-label">{t}New password{/t}</label>
            <div class="col-sm-8">
                <input type="password" class="form-control" id="inputPasswordNew" name="password_new" placeholder="{t}New password{/t}" required>
            </div>
        </div>

        <div class="form-group">
            <label for="inputPasswordConfirm" class="col-sm-2 control-label">{t}Confirm password{/t}</label>
            <div class="col-sm-8">
                <input type="password" class="form-control" id="inputPasswordConfirm" name="password_new_confirm" placeholder="{t}Confirm password{/t}" data-parsley-equalto="#inputPasswordNew" required>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <div class="col-sm-10 col-sm-offset-2">
                <h4>{t}Read more about choosing good passwords:{/t}</h4>
                <ul>
                    <li><a href="http://hitachi-id.com/password-manager/docs/choosing-good-passwords.html" target="_blank">{t}Choosing Good Passwords -- A User Guide{/t}</a> {fa icon="external-link"}</li>
                    <li><a href="http://www.csoonline.com/article/220721/how-to-write-good-passwords" target="_blank">{t}How to Write Good Passwords{/t}</a> {fa icon="external-link"}</li>
                    <li><a href="http://en.wikipedia.org/wiki/Password_strength" target="_blank">{t}Password strength{/t}</a> {fa icon="external-link"}</li>
                </ul>
            </div>
        </div>

    </div>

    <div class="modal-footer">
        {call csrf}
        <a class="btn btn-default" href="{'user_details'|alias:sprintf('userid=%d', $currentUser->id)}" title="{t}Back to user page{/t}" data-dismiss="modal">
            {t}Dismiss{/t}
        </a>
        <button type="submit" class="btn btn-primary">{t}Change{/t}</button>
    </div>
</form>
{/block}

{block name=javascript}
// Initialize ZXCVBN
$('#inputPasswordNew').strengthify({
    zxcvbn: '{GK_CDN_ZXCVBN_JS}'
});
{/block}
