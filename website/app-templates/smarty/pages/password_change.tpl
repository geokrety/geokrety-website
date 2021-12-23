{extends file='base.tpl'}

{block name=title}{t}Change your password{/t}{/block}

{\Assets::instance()->addCss(GK_CDN_STRENGTHIFY_CSS)}
{\Assets::instance()->addJs(GK_CDN_STRENGTHIFY_JS)}

{block name=content}

{if ($f3->exists('POST.token') || !$token->dry() || is_null($token->token))}
<div class="panel panel-default">
    <div class="panel-heading">
        {t}Change your password{/t}
    </div>
    <div class="panel-body">

        <form class="form-horizontal" action="" method="post" id="formPasswordRecover" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>

            {if $token->dry()}
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <p class="form-control-static"><em>{t}Please enter the verification token received by mail.{/t}</em></p>
                </div>
            </div>

            <div class="form-group">
                <label for="inputVerificationToken" class="col-sm-2 control-label">{t}Verification Token{/t}</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="inputVerificationToken" name="token" value="{$token->token}" placeholder="{t}Verification Token{/t}" minlength="{GK_SITE_PASSWORD_RECOVERY_CODE_LENGTH}" maxlength="{GK_SITE_PASSWORD_RECOVERY_CODE_LENGTH}" required>
                </div>
            </div>
            {/if}

            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <p class="form-control-static"><em>{t}Please define a new password.{/t}</em></p>
                </div>
            </div>

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

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {call csrf}
                    <button type="submit" id="changePasswordButton" class="btn btn-primary">{t}Change{/t}</button>
                </div>
            </div>
        </form>

    </div>
</div>
{/if}

{/block}

{block name=javascript}
// Initialize ZXCVBN
$('#inputPasswordNew').strengthify({
    zxcvbn: '{GK_CDN_ZXCVBN_JS}'
});
{/block}
