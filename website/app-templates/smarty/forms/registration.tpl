<form class="form-horizontal" action="" method="post" data-parsley-validate data-parsley-ui-enabled=true>

    <div class="form-group">
        <label for="usernameInput" class="col-sm-2 control-label">{t}Username{/t}</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="usernameInput" name="username" placeholder="{t}Username{/t}" value="{$user->username}" required minlength="{GK_SITE_USERNAME_MIN_LENGTH}" maxlength="{GK_SITE_USERNAME_MAX_LENGTH}" data-parsley-trigger="focusout" data-parsley-remote data-parsley-remote-validator="usernameFreeValidator" data-parsley-remote-options='{ "type": "POST" }' data-parsley-errors-messages-disabled data-parsley-debounce="500">
        </div>
    </div>

    <div class="form-group">
        <label for="emailInput" class="col-sm-2 control-label">{t}Email address{/t}</label>
        <div class="col-sm-8">
            <input type="email" class="form-control" id="emailInput" name="email" placeholder="{t}Email address{/t}" value="{$user->email}" aria-describedby="emailHelpBlock" required>
            <span id="emailHelpBlock" class="help-block">{t}The main purpose of collecting email is to permit password recovery.{/t}</span>
        </div>
    </div>

    <div class="form-group">
        <label for="passwordInput" class="col-sm-2 control-label">{t}Password{/t}</label>
        <div class="col-sm-8">
            <input type="password" class="form-control" id="passwordInput" name="password" placeholder="{t}Password{/t}" minlength="{GK_SITE_USER_PASSWORD_MIN_LENGTH}" required>
        </div>
    </div>

    <div class="form-group">
        <label for="passwordConfirmInput" class="col-sm-2 control-label">{t}Confirm password{/t}</label>
        <div class="col-sm-8">
            <input type="password" class="form-control" id="passwordConfirmInput" name="password_confirm" placeholder="{t}Confirm password{/t}" data-parsley-equalto="#passwordInput" required>
        </div>
    </div>

    <div class="form-group">
        <label for="preferredLanguageInput" class="col-sm-2 control-label">{t}Language{/t}</label>
        <div class="col-sm-8">
            <select class="form-control" id="preferredLanguageInput" name="preferred_language" aria-describedby="preferredLanguageHelpBlock" required>
                {foreach $languages as $code => $lang}
                <option value="{$code}" {if $user->preferred_language === $code or \Multilang::instance()->current === $code} selected{/if}>{$lang}</option>
                {/foreach}
            </select>
            <span id="preferredLanguageHelpBlock" class="help-block">{t}This will be the default language when you log in and the main language in the emails you may receive.{/t}</span>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="dailyMailsInput" name="daily_mails" aria-describedby="dailyMailsHelpBlock" {if $user->daily_mails} checked{/if}>{t}Yes, I want to receive email alerts (sent once a day).{/t}
                    <span id="dailyMailsHelpBlock" class="help-block">{t}Email alerts may contains, travel information about your GeoKrety, news, comments…{/t}</span>
                </label>
            </div>
        </div>
    </div>

    {if GK_PIWIK_ENABLED}
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="trackingOptInInput" name="tracking_opt_in">
                    {t}Opt-in to site usage analytics.{/t}
                    <span id="dailyMailsHelpBlock" class="help-block">{t}We collect site usage analytics, this help us understanding how the site is used and how to enhance it.{/t}</span>
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
            <button id="registerButton" type="submit" class="btn btn-primary">{t}Register{/t}</button>
        </div>
    </div>
</form>
