{extends file='base.tpl'}

{block name=title}{t}Changing your email address{/t}{/block}

{block name=content}

{if ($f3->exists('POST.token') || !$token->dry() || is_null($token->token))}
<div class="panel panel-default">
    <div class="panel-heading">
        {t}Email address revalidation.{/t}
    </div>
    <div class="panel-body">

        <form class="form-horizontal" method="post" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>

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

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {if $f3->get('SESSION.CURRENT_USER')}
                    <a class="btn btn-default" href="{'user_details'|alias:sprintf('@userid=%d', $f3->get('SESSION.CURRENT_USER'))}" title="{t}Back to your profile{/t}">
                        {t}Dismiss{/t}
                    </a>
                    {else}
                    <a class="btn btn-default" href="{'home'|alias}" title="{t}Back to homepage{/t}">
                        {t}Dismiss{/t}
                    </a>
                    {/if}
                    <button type="submit" class="btn btn-primary">{t}Check the token{/t}</button>
                </div>
            </div>
        </form>

    </div>
</div>
{/if}

{/block}
