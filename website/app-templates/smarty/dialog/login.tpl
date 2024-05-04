
{block name=title}{t}Login{/t}{/block}

{block name=modal_content}
<div class="modal-header alert-info">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Login{/t}</h4>
</div>

<div class="modal-body">
    <form action="{'login'|alias}{if $f3->exists('GET')}?{http_build_query($f3->get('GET')) nofilter}{/if}" method="post" class="form-horizontal" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>
    {if GK_OPAUTH_ACTIVE}
        <div class="form-group">
            <label class="col-sm-2 control-label">{t}Login using{/t}</label>
            <div class="col-sm-8">
                <div class="btn-group" role="group">
                    {if GK_OPAUTH_FACEBOOK_CLIENT_ID !== false}
                        <a id="btn-facebookauth" href="/auth/facebook" class="btn btn-primary">
                            {fa icon="facebook"} facebook
                        </a>
                    {/if}
                    {if GK_OPAUTH_GITHUB_CLIENT_ID !== false}
                        <a id="btn-githubauth" href="/auth/github" class="btn btn-default btn-black">
                            {fa icon="github"} github
                        </a>
                    {/if}
                    {if GK_OPAUTH_GOOGLE_CLIENT_ID !== false}
                        <a id="btn-googleauth" href="/auth/google" class="btn btn-danger">
                            {fa icon="google"} google
                        </a>
                    {/if}
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="separator">{t}Or{/t}</div>
        </div>
    {/if}

        <div class="form-group">
            <label for="inputUsername" class="col-sm-2 control-label">{t}Username{/t}</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="inputUsername" name="username" placeholder="{t}Username{/t}" maxlength="30" value="{if isset($smarty.post.username)}{$smarty.post.username}{/if}" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label for="inputPassword" class="col-sm-2 control-label">{t}Password{/t}</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="inputPassword" name="password" placeholder="{t}Password{/t}" maxlength="80" required>
            </div>
        </div>
{*        <div class="form-group">*}
{*            <div class="col-sm-offset-2 col-sm-10">*}
{*                <div class="checkbox">*}
{*                    <label>*}
{*                        <input id="rememberMeCheckbox" name="remember" type="checkbox"> {t}Remember me{/t}*}
{*                    </label>*}
{*                    <p class="help-block">*}
{*                        {t escape=no url={'help'|alias:null:null:'#cookies'}}We are using cookies only to keep your session active. Read more about our <a href="%1" target="_blank">cookies policy</a>.{/t}*}
{*                    </p>*}
{*                </div>*}
{*            </div>*}
{*        </div>*}
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-3">
                {call csrf}
                <button type="submit" id="signInButton" class="btn btn-primary">{t}Sign in{/t}</button>
            </div>
            <div class="col-sm-5">
                <a href="{'registration'|alias}">{t}No account yet ? Register now!{/t}</a>
            </div>
            <div class="col-sm-2">
                <p>
                    <a href="{'password_recovery'|alias}">{t}Forgot your password?{/t}</a>
                </p>
            </div>
        </div>

    </form>
</div>
{/block}
