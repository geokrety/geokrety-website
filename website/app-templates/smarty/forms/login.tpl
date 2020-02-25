{block name=content}
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{t}Login{/t}</h3>
            </div>
            <div class="panel-body">

                <form action="{'login'|alias}{if $f3->exists('GET')}?{http_build_query($f3->get('GET')) nofilter}{/if}" method="post" class="form-horizontal" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>

                    <div class="form-group">
                        <label for="inputUsername" class="col-sm-2 control-label">{t}Username{/t}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputUsername" name="login" placeholder="{t}Username{/t}" maxlength="30" value="{if isset($smarty.post.login)}{$smarty.post.login}{/if}" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword" class="col-sm-2 control-label">{t}Password{/t}</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="inputPassword" name="password" placeholder="{t}Password{/t}" maxlength="80" required>
                        </div>
                    </div>
                    <!-- <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <div class="checkbox">
                                <label>
                                    <input id="remember" name="remember" type="checkbox"> {t}Remember me{/t}
                                </label>
                                <p class="help-block">
                                    {t escape=no url="help.php#cookies"}We are using cookies only for storing login information and language preferences. Read more about our <a href="%1">cookies policy</a>.{/t}
                                </p>
                            </div>
                        </div>
                    </div> -->
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">{t}Sign in{/t}</button>
                            <a href="{'registration'|alias}">{t}No account yet ? Register now!{/t}</a>
                            <div class="pull-right">
                                <p>
                                    <a href="{'password_recovery'|alias}">{t}Forgot your password?{/t}</a>
                                </p>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
{/block}
