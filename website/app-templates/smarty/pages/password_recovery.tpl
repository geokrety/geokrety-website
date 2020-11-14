{extends file='base.tpl'}

{block name=title}{t}Password recovery{/t}{/block}

{if GK_GOOGLE_RECAPTCHA_JS_URL}{\Assets::instance()->addJs(GK_GOOGLE_RECAPTCHA_JS_URL)}{/if}

{block name=content}
<div class="panel panel-default">
    <div class="panel-heading">
        {t}Password recovery{/t}
    </div>
    <div class="panel-body">

        <form class="form-horizontal" action="" method="post" id="formPasswordRecovery" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>


            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <p class="form-control-static">{t}If you have validated your mail address in the past, then you can recover it easily. Else, you'll have to create another account, sorry.{/t}</p>
                </div>
            </div>

            <div class="form-group">
                <label for="content" class="col-sm-2 control-label">{t}Email address{/t}</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="email" name="email" placeholder="{t}Email address{/t}" value="{$user->email}" required>
                </div>
            </div>

            {if GK_GOOGLE_RECAPTCHA_PUBLIC_KEY}
            <div class="form-group">
                <div class="col-sm-10 col-sm-offset-2">
                    <div class="g-recaptcha" data-sitekey="{GK_GOOGLE_RECAPTCHA_PUBLIC_KEY}" id="recaptcha_wrapper"></div>
                </div>
            </div>
            {/if}

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" id="sendRecoveryLinkButton" class="btn btn-primary">{t}Send me a recovery link{/t}</button>
                </div>
            </div>
        </form>

    </div>
</div>
{/block}
