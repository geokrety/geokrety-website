<ol class="breadcrumb">
    <li><a href="/">{t}Home{/t}</a></li>
    <li class="active">{t}Register a new user{/t}</li>
</ol>


<div class="row">
    <div class="col-md-6 col-md-offset-3">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{t}Register a new user{/t}</h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" id="createUser">

                    <div class="form-group">
                        <label for="inputUsername" class="col-sm-2 control-label">{t}Username{/t}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="inputUsername" name="inputUsername" placeholder="{t}Username{/t}" minlength="3" maxlength="30" value="{if isset($smarty.post.inputUsername)}{$smarty.post.inputUsername}{/if}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputPassword" class="col-sm-2 control-label">{t}Password{/t}</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="inputPassword" name="inputPassword" placeholder="{t}Password{/t}" minlength="5" maxlength="80" value="{if isset($smarty.post.inputPassword)}{$smarty.post.inputPassword}{/if}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPasswordConfirm" class="col-sm-2 control-label">{t}Confirm password{/t}</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control" id="inputPasswordConfirm" name="inputPasswordConfirm" placeholder="{t}Confirm password{/t}" minlength="5" maxlength="80" value="{if isset($smarty.post.inputPasswordConfirm)}{$smarty.post.inputPasswordConfirm}{/if}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputEmail" class="col-sm-2 control-label">{t}Email address{/t}</label>
                        <div class="col-sm-10">
                            <input type="email" class="form-control" id="inputEmail" name="inputEmail" placeholder="{t}Email{/t}" value="{if isset($smarty.post.inputEmail)}{$smarty.post.inputEmail}{/if}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <div class="checkbox">
                                <label>
                                    <input id="dailymail" name="dailymail" type="checkbox"{if !isset($smarty.post.dailymail) or $smarty.post.dailymail == 'on'} checked{/if}> {t}Subscribe to daily alerts{/t}
                                </label>
                                <p class="help-block">
                                    {t}Yes, I want to receive daily email alerts when my or watched GeoKrety changes its location.{/t}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inputIdiom" class="col-sm-2 control-label">{t}Idiom{/t}</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="inputIdiom" name="language">
                                {foreach $languages as $code => $lang}
                                <option value="{$code}"{if isset($smarty.post.language) and $smarty.post.language == $code} selected{/if}>{$lang}</option>
                                {/foreach}
                            </select>
                            <p class="help-block">
                                {t}This will be the default language when you log in and the main language in the emails you may receive.{/t}
                            </p>
                        </div>
                    </div>

{if isset($GOOGLE_RECAPTCHA_PUBLIC_KEY)}
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-2">
                            <div class="g-recaptcha" data-sitekey="{$GOOGLE_RECAPTCHA_PUBLIC_KEY}" id="recaptcha_wrapper"></div>
                        </div>
                    </div>
{/if}

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">{t}Register a new user{/t}</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-5 col-sm-offset-2">
                            <p class="form-control-static"><a href="termsofuse.php">{t}Terms of use{/t}</a></p>
                        </div>
                        <div class="col-sm-5">
                            <p class="form-control-static"><a href="/longin.php">{t}Already have an account?{/t}</a></p>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
