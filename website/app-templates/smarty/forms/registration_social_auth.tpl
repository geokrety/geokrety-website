<form class="form-horizontal" action="" method="post" data-parsley-validate data-parsley-ui-enabled=true>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {t escape=no provider=$social_auth_data->provider login={'login'|alias}}If you already own an account on Geokrety.org, please <a href="%2">login</a> first, then associate your account with %1 from the user settings page. Else please choose a username to proceed.{/t}
        </div>
    </div>

    <div class="form-group">
        <label for="usernameInput" class="col-sm-2 control-label">{t}Username{/t}</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="usernameInput" name="username" placeholder="{t}Username{/t}" value="{$user->username}" required minlength="{GK_SITE_USERNAME_MIN_LENGTH}" maxlength="{GK_SITE_USERNAME_MAX_LENGTH}" data-parsley-trigger="focusout" data-parsley-remote data-parsley-remote-validator="usernameFreeValidator" data-parsley-remote-options='{ "type": "POST" }' data-parsley-errors-messages-disabled>
        </div>
    </div>

{*    {if !$user->hasEmail()}*}
{*    <div class="form-group">*}
{*        <label for="emailInput" class="col-sm-2 control-label">{t}Email address{/t}</label>*}
{*        <div class="col-sm-8">*}
{*            <input type="email" class="form-control" id="emailInput" name="email" placeholder="{t}Email address{/t}" value="{$user->email}" aria-describedby="emailHelpBlock" required>*}
{*            <span id="emailHelpBlock" class="help-block">{t}The main purpose of collecting email is to permit password recovery.{/t}</span>*}
{*        </div>*}
{*    </div>*}
{*    {/if}*}

    {if $user->hasEmail()}
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="daily_mails" aria-describedby="dailyMailsHelpBlock" {if $user->daily_mails} checked{/if}>{t}Yes, I want to receive email alerts (sent once a day).{/t}
                    <span id="dailyMailsHelpBlock" class="help-block">{t}Email alerts may contains, travel information about your GeoKrety, news, comments…{/t}</span>
                </label>
            </div>
        </div>
    </div>
    {/if}

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="termsOfUseInput" name="terms_of_use" required {if $user->terms_of_use_datetime} checked{/if}>
                    {t escape=no}Yes, I accept the <a href="#" data-toggle="modal" data-target="#modal" data-type="terms-of-use">terms of use</a>.{/t}
                </label>
            </div>
        </div>
    </div>

    {call recaptcha}

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            {call csrf}
            <button type="submit" class="btn btn-primary">{t}Register{/t}</button>
        </div>
    </div>
</form>
